<?php

namespace App\Services;

use App\Models\BarangBatch;
use App\Models\BarangKeluarBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class FifoService
{
    public function getAvailableBatches(int $barangId, ?string $excludeExpired = null): Collection
    {
        $query = BarangBatch::where('barang_id', $barangId)
            ->where('qty_tersisa', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->orderBy('created_at', 'asc');

        if ($excludeExpired) {
            $query->where(function (Builder $q) {
                $q->whereNull('expired_date')
                  ->orWhere('expired_date', '>', now());
            });
        }

        return $query->get();
    }

    public function checkStockAvailability(int $barangId, int $qtyNeeded): array
    {
        $availableBatches = $this->getAvailableBatches($barangId, 'exclude_expired');
        $totalAvailable = $availableBatches->sum('qty_tersisa');

        return [
            'available' => $totalAvailable >= $qtyNeeded,
            'total_available' => $totalAvailable,
            'needed' => $qtyNeeded,
            'shortage' => max(0, $qtyNeeded - $totalAvailable),
            'batches' => $availableBatches
        ];
    }

    public function calculateFifoAllocation(int $barangId, int $qtyNeeded): array
    {
        $stockCheck = $this->checkStockAvailability($barangId, $qtyNeeded);
        
        if (!$stockCheck['available']) {
            throw new \Exception("Stok tidak mencukupi. Tersedia: {$stockCheck['total_available']}, Dibutuhkan: {$qtyNeeded}");
        }

        $batches = $stockCheck['batches'];
        $allocation = [];
        $remainingQty = $qtyNeeded;
        $totalNilai = 0;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $qtyFromThisBatch = min($remainingQty, $batch->qty_tersisa);
            $nilaiFromThisBatch = $qtyFromThisBatch * $batch->harga_satuan;

            $allocation[] = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'qty_digunakan' => $qtyFromThisBatch,
                'qty_tersisa_sebelum' => $batch->qty_tersisa,
                'qty_tersisa_setelah' => $batch->qty_tersisa - $qtyFromThisBatch,
                'nilai_satuan' => $batch->harga_satuan,
                'total_nilai' => $nilaiFromThisBatch,
                'tanggal_masuk' => $batch->tanggal_masuk,
                'expired_date' => $batch->expired_date,
            ];

            $remainingQty -= $qtyFromThisBatch;
            $totalNilai += $nilaiFromThisBatch;
        }

        return [
            'allocation' => $allocation,
            'total_qty' => $qtyNeeded,
            'total_nilai' => $totalNilai,
            'average_cost' => $qtyNeeded > 0 ? $totalNilai / $qtyNeeded : 0,
        ];
    }

    

    public function calculateFifoAllocationWithDetails(int $barangId, int $qtyRequested): array
    {
        $batches = DB::table('barang_batch as bb')
            ->leftJoin('pemasok as p', 'bb.pemasok_id', '=', 'p.id')
            ->where('bb.barang_id', $barangId)
            ->where('bb.qty_tersisa', '>', 0)
            ->orderBy('bb.tanggal_masuk', 'asc')
            ->orderBy('bb.id', 'asc')
            ->select(
                'bb.id',
                'bb.batch_code',
                'bb.qty_tersisa',
                'bb.harga_satuan',
                'bb.tanggal_masuk',
                'bb.expired_date',
                'p.nama_pemasok'
            )
            ->get();

        $allocation = [];
        $totalCost = 0;
        $qtyRemaining = $qtyRequested;
        $batchDetails = [];

        foreach ($batches as $batch) {
            if ($qtyRemaining <= 0) break;

            $qtyToTake = min($qtyRemaining, $batch->qty_tersisa);
            $batchCost = $qtyToTake * $batch->harga_satuan;

            $expiryStatus = $this->getExpiryStatus($batch->expired_date);

            $allocation[] = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'qty_digunakan' => $qtyToTake,
                'harga_satuan' => $batch->harga_satuan,
                'subtotal' => $batchCost
            ];

            $batchDetails[] = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'qty_digunakan' => $qtyToTake,
                'qty_tersisa_awal' => $batch->qty_tersisa,
                'harga_satuan' => $batch->harga_satuan,
                'subtotal' => $batchCost,
                'tanggal_masuk' => $batch->tanggal_masuk,
                'expired_date' => $batch->expired_date,
                'status_expired' => $expiryStatus,
                'pemasok_nama' => $batch->nama_pemasok
            ];

            $totalCost += $batchCost;
            $qtyRemaining -= $qtyToTake;
        }

        $averageCost = $qtyRequested > 0 ? $totalCost / $qtyRequested : 0;

        return [
            'allocation' => $allocation,
            'batch_details' => $batchDetails,
            'total_nilai' => $totalCost,
            'average_cost' => $averageCost,
            'qty_fulfilled' => $qtyRequested - $qtyRemaining,
            'qty_shortage' => $qtyRemaining
        ];
    }

    private function getExpiryStatus($expiredDate): string
    {
        if (!$expiredDate) return 'no_expiry';

        $now = now();
        $expired = Carbon::parse($expiredDate);
        $daysUntilExpiry = $now->diffInDays($expired, false);

        if ($daysUntilExpiry < 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 30) {
            return 'near_expired';
        } else {
            return 'good';
        }
    }

    public function processFifoBarangKeluar(int $barangKeluarDetailId, int $barangId, int $qty): array
    {
        return DB::transaction(function () use ($barangKeluarDetailId, $barangId, $qty) {
            $fifoData = $this->calculateFifoAllocation($barangId, $qty);
            $processedBatches = [];

            foreach ($fifoData['allocation'] as $allocation) {
                BarangKeluarBatch::create([
                    'barang_keluar_detail_id' => $barangKeluarDetailId,
                    'barang_batch_id' => $allocation['batch_id'],
                    'qty_digunakan' => $allocation['qty_digunakan'],
                    'nilai_satuan' => $allocation['nilai_satuan'],
                    'total_nilai' => $allocation['total_nilai'],
                ]);

                $affected = BarangBatch::where('id', $allocation['batch_id'])
                    ->where('qty_tersisa', '>=', $allocation['qty_digunakan'])
                    ->lockForUpdate()
                    ->decrement('qty_tersisa', $allocation['qty_digunakan']);

                if ($affected === 0) {
                    throw new \Exception("Concurrent access detected on batch {$allocation['batch_code']}");
                }

                $processedBatches[] = [
                    'batch_code' => $allocation['batch_code'],
                    'qty_used' => $allocation['qty_digunakan'],
                    'remaining' => $allocation['qty_tersisa_setelah'],
                    'cost' => $allocation['nilai_satuan'],
                ];
            }

            return [
                'processed_batches' => $processedBatches,
                'total_cost' => $fifoData['total_nilai'],
                'average_cost' => $fifoData['average_cost'],
            ];
        });
    }

    public function getFifoCostPreview(array $barangItems): array
    {
        $preview = [];
        $grandTotal = 0;

        foreach ($barangItems as $item) {
            try {
                $fifoData = $this->calculateFifoAllocationWithDetails($item['barang_id'], $item['qty']);

                $preview[] = [
                    'barang_id' => $item['barang_id'],
                    'qty' => $item['qty'],
                    'total_cost' => $fifoData['total_nilai'],
                    'average_cost' => $fifoData['average_cost'],
                    'batches_used' => count($fifoData['batch_details']),
                    'batch_details' => $fifoData['batch_details'],
                ];

                $grandTotal += $fifoData['total_nilai'];
            } catch (\Exception $e) {
                $preview[] = [
                    'barang_id' => $item['barang_id'],
                    'qty' => $item['qty'],
                    'error' => $e->getMessage(),
                    'total_cost' => 0,
                    'average_cost' => 0,
                ];
            }
        }

        return [
            'items' => $preview,
            'grand_total' => $grandTotal,
        ];
    }

    public function getBatchDetails(int $barangId): Collection
    {
        return BarangBatch::where('barang_id', $barangId)
            ->where('qty_tersisa', '>', 0)
            ->with(['pemasok:id,nama_pemasok'])
            ->orderBy('tanggal_masuk', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'batch_code' => $batch->batch_code,
                    'qty_tersisa' => $batch->qty_tersisa,
                    'tanggal_masuk' => $batch->tanggal_masuk->format('d/m/Y'),
                    'expired_date' => $batch->expired_date ? $batch->expired_date->format('d/m/Y') : '-',
                    'harga_satuan' => $batch->harga_satuan,
                    'pemasok' => $batch->pemasok->nama_pemasok ?? '-',
                    'status_expired' => $batch->expired_date ? 
                        ($batch->expired_date <= now() ? 'expired' : 
                        ($batch->expired_date <= now()->addDays(30) ? 'near_expired' : 'good')) : 'no_expiry'
                ];
            });
    }

    public function validateMultipleItems(array $items): array
    {
        $validationResults = [];
        $hasError = false;

        foreach ($items as $item) {
            $stockCheck = $this->checkStockAvailability($item['barang_id'], $item['qty']);

            $validationResults[] = [
                'barang_id' => $item['barang_id'],
                'qty_requested' => $item['qty'],
                'qty_available' => $stockCheck['total_available'],
                'is_sufficient' => $stockCheck['available'],
                'shortage' => $stockCheck['shortage'],
            ];

            if (!$stockCheck['available']) {
                $hasError = true;
            }
        }

        return [
            'valid' => !$hasError,
            'items' => $validationResults,
        ];
    }
}
