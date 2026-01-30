<?php

namespace App\Livewire;

use App\Models\Barang;
use Livewire\Component;
use App\Models\BarangBatch;
use Illuminate\Support\Str;
use App\Models\BarangKeluar;
use App\Services\FifoService;
use App\Models\BarangKeluarDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditTransaksiBarangKeluar extends Component
{
    public $barangKeluar;
    public $barangKeluarId;
    public $tgl_keluar, $jenis_keluar = 'distribusi', $keterangan;
    public $barangDetails = [];
    public $showFifoPreview = false;
    public $fifoPreview = [];
    public $selectedBarangBatches = [];
    public $originalDetails = [];

    protected $fifoService;

    public function boot(FifoService $fifoService)
    {
        $this->fifoService = $fifoService;
    }

    protected $rules = [
        'tgl_keluar' => 'required|date|before_or_equal:today',
        'jenis_keluar' => 'required|in:distribusi,retur,rusak,expired,hilang',
        'barangDetails.*.barang_id' => 'required|exists:barang,id',
        'barangDetails.*.qty' => 'required|numeric|min:1',
    ];

    protected $messages = [
        'barangDetails.*.barang_id.required' => 'Barang harus dipilih',
        'barangDetails.*.qty.min' => 'Qty minimal 1',
        'tgl_keluar.before_or_equal' => 'Tanggal keluar tidak boleh di masa depan',
    ];

    public function mount($id)
    {
        $this->barangKeluarId = $id;
        $this->barangKeluar = BarangKeluar::with('details.barang')->findOrFail($id);
        
        // Load existing data
        $this->tgl_keluar = $this->barangKeluar->tgl_keluar;
        $this->jenis_keluar = $this->barangKeluar->jenis_keluar;
        $this->keterangan = $this->barangKeluar->keterangan;
        
        // Load existing details
        $this->loadExistingDetails();
        
        // Store original details for comparison
        $this->originalDetails = collect($this->barangDetails)->map(function ($detail) {
            return [
                'barang_id' => $detail['barang_id'],
                'qty' => $detail['qty'],
                'detail_id' => $detail['detail_id'] ?? null,
            ];
        })->toArray();

        // Initialize FIFO preview to show total nilai on mount
        $this->updateFifoPreview();
    }

    private function loadExistingDetails()
    {
        $this->barangDetails = [];
        
        foreach ($this->barangKeluar->details as $detail) {
            $barang = $detail->barang;
            
            // Get current stock plus the quantity from this transaction
            $currentStock = $barang->stok + $detail->qty;
            
            // Get batch information
            $batchDetails = $this->fifoService->getBatchDetails($detail->barang_id);
            
            $this->barangDetails[] = [
                'detail_id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $barang->nama_barang,
                'satuan' => $barang->satuan ?? 'pcs',
                'qty' => $detail->qty,
                'original_qty' => $detail->qty, // Store original quantity
                'stok_tersedia' => $currentStock,
                'estimasi_nilai' => $detail->total_nilai,
                'batch_info' => $batchDetails->toArray(),
            ];
            
            $this->selectedBarangBatches[$detail->barang_id] = $batchDetails;
        }
        
        // If no details, add one empty row
        if (empty($this->barangDetails)) {
            $this->barangDetails[] = $this->newBarangDetailRow();
        }
    }

    public function newBarangDetailRow()
    {
        return [
            'detail_id' => null,
            'barang_id' => null,
            'nama_barang' => '',
            'satuan' => '',
            'qty' => 1,
            'original_qty' => 0,
            'stok_tersedia' => 0,
            'estimasi_nilai' => 0,
            'batch_info' => [],
        ];
    }

    public function addRow()
    {
        $this->barangDetails[] = $this->newBarangDetailRow();
    }

    public function removeRow($index)
    {
        if (count($this->barangDetails) > 1) {
            unset($this->barangDetails[$index]);
            $this->barangDetails = array_values($this->barangDetails);
            $this->updateFifoPreview();
        }
    }

    public function updatedBarangDetails($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'barang_id' && $value) {
            $this->loadBarangInfo($index, $value);
        }

        if ($field === 'qty' || $field === 'barang_id') {
            $this->updateFifoPreview();
        }
    }

    private function loadBarangInfo($index, $barangId)
    {
        $barang = Barang::find($barangId);
        if ($barang) {
            $this->barangDetails[$index]['nama_barang'] = $barang->nama_barang;
            $this->barangDetails[$index]['satuan'] = $barang->satuan ?? 'pcs';
            
            // If this is an existing detail being changed, add back the original quantity
            $originalQty = $this->barangDetails[$index]['original_qty'] ?? 0;
            $this->barangDetails[$index]['stok_tersedia'] = $barang->stok + $originalQty;

            // Get batch information
            $batchDetails = $this->fifoService->getBatchDetails($barangId);
            $this->barangDetails[$index]['batch_info'] = $batchDetails->toArray();
            $this->selectedBarangBatches[$barangId] = $batchDetails;
        }
    }

    public function updateFifoPreview()
    {
        $items = collect($this->barangDetails)
            ->filter(fn($item) => $item['barang_id'] && $item['qty'] > 0)
            ->map(fn($item) => [
                'barang_id' => $item['barang_id'],
                'qty' => $item['qty']
            ])
            ->toArray();

        if (!empty($items)) {
            $this->fifoPreview = $this->fifoService->getFifoCostPreview($items);
            
            // Update estimasi nilai per item
            foreach ($this->fifoPreview['items'] as $previewItem) {
                $index = collect($this->barangDetails)->search(function ($item) use ($previewItem) {
                    return $item['barang_id'] == $previewItem['barang_id'];
                });
                
                if ($index !== false) {
                    $this->barangDetails[$index]['estimasi_nilai'] = $previewItem['total_cost'] ?? 0;
                }
            }
        } else {
            $this->fifoPreview = ['items' => [], 'grand_total' => 0];
        }
    }

    public function toggleFifoPreview()
    {
        $this->showFifoPreview = !$this->showFifoPreview;
        if ($this->showFifoPreview) {
            $this->updateFifoPreview();
        }
    }

    public function validateStock()
    {
        $items = collect($this->barangDetails)
            ->filter(fn($item) => $item['barang_id'] && $item['qty'] > 0)
            ->map(function ($item) {
                // PERUBAHAN PENTING: Hitung stok tersedia termasuk stok yang akan dikembalikan
                $availableStock = Barang::find($item['barang_id'])->stok + $item['original_qty'];
                
                return [
                    'barang_id' => $item['barang_id'],
                    'qty_requested' => $item['qty'],
                    'qty_available' => $availableStock,
                    'is_sufficient' => $availableStock >= $item['qty'],
                    'shortage' => max(0, $item['qty'] - $availableStock),
                    'original_qty' => $item['original_qty'] ?? 0,
                ];
            })
            ->toArray();

        if (empty($items)) {
            session()->flash('error', 'Tidak ada item yang akan dikeluarkan.');
            return false;
        }

        $hasError = false;
        $validationResults = [];

        foreach ($items as $item) {
            $validationResults[] = $item;
            
            if (!$item['is_sufficient']) {
                $barang = Barang::find($item['barang_id']);
                session()->flash('error', 
                    "Stok tidak mencukupi untuk {$barang->nama_barang}. " .
                    "Tersedia: {$item['qty_available']}, Diminta: {$item['qty_requested']}"
                );
                $hasError = true;
            }
        }

        return !$hasError;
    }

    

    public function update()
    {
        $this->validate();

        if (!$this->validateStock()) {
            return;
        }

        DB::beginTransaction();

        try {
              // PERBAIKAN: Simpan dulu batch asli sebelum di-restore
            $originalBatches = $this->getOriginalBatchData();

            // First, restore stock from original transaction
            $this->restoreOriginalStock();

            // Calculate totals from FIFO preview
            $this->updateFifoPreview();
            $totalQty = collect($this->barangDetails)->sum('qty');
            $totalNilai = $this->fifoPreview['grand_total'] ?? 0;

            // Update barang keluar header
            $this->barangKeluar->update([
                'tgl_keluar' => $this->tgl_keluar,
                'total_qty' => $totalQty,
                'total_nilai' => $totalNilai,
                'jenis_keluar' => $this->jenis_keluar,
                'keterangan' => $this->keterangan,
            ]);

            // Delete existing details and their FIFO records
            $this->deleteExistingDetails();

            // Process each new item
            foreach ($this->barangDetails as $detail) {
                if (!$detail['barang_id'] || $detail['qty'] <= 0) continue;

                // Get FIFO calculation
                $fifoData = $this->fifoService->calculateFifoAllocation(
                    $detail['barang_id'], 
                    $detail['qty']
                );

                // Create new barang keluar detail
                $barangKeluarDetail = BarangKeluarDetail::create([
                    'barang_keluar_id' => $this->barangKeluar->id,
                    'barang_id' => $detail['barang_id'],
                    'qty' => $detail['qty'],
                    'nilai_satuan' => $fifoData['average_cost'],
                    'total_nilai' => $fifoData['total_nilai'],
                ]);

                // Process FIFO and update batches
                $this->fifoService->processFifoBarangKeluar(
                    $barangKeluarDetail->id,
                    $detail['barang_id'],
                    $detail['qty']
                );

                // Update main barang stock
                $barang = Barang::find($detail['barang_id']);
                $barang->decrement('stok', $detail['qty']);
            }

            DB::commit();

            session()->flash('success', "Transaksi barang keluar berhasil diperbarui.");
            return redirect()->route('barang-keluar.show', $this->barangKeluar->id);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
            Log::error('Error updating barang keluar: ' . $e->getMessage());
        }
    }

    // Fungsi baru untuk mendapatkan data batch asli
    private function getOriginalBatchData(): array
    {
        $batchData = [];

        foreach ($this->barangKeluar->details as $detail) {
            foreach ($detail->batch as $batch) {
                $batchData[] = [
                    'batch_id' => $batch->barang_batch_id,
                    'qty_digunakan' => $batch->qty_digunakan,
                ];
            }
        }

        return $batchData;
    }

    private function restoreOriginalStock()
    {
        // PERBAIKAN: Restore stok batch terlebih dahulu
        foreach ($this->barangKeluar->details as $detail) {
            foreach ($detail->batch as $batch) {
                BarangBatch::where('id', $batch->barang_batch_id)
                    ->increment('qty_tersisa', $batch->qty_digunakan);
            }
        }

        // Restore main barang stock
        foreach ($this->barangKeluar->details as $detail) {
            $barang = Barang::find($detail->barang_id);
            if ($barang) {
                $barang->increment('stok', $detail->qty);
            }
        }
    }

    private function deleteExistingDetails()
    {
        // Delete FIFO records first
        DB::table('barang_keluar_batch')
            ->whereIn('barang_keluar_detail_id', 
                $this->barangKeluar->details->pluck('id')->toArray()
            )
            ->delete();

        // Delete details
        $this->barangKeluar->details()->delete();
    }

    public function getTotalQtyProperty()
    {
        return collect($this->barangDetails)->sum('qty');
    }

    public function getTotalNilaiProperty()
    {
        return $this->fifoPreview['grand_total'] ?? 0;
    }

    public function resetForm()
    {
        $this->loadExistingDetails();
        $this->tgl_keluar = $this->barangKeluar->tgl_keluar;
        $this->jenis_keluar = $this->barangKeluar->jenis_keluar;
        $this->keterangan = $this->barangKeluar->keterangan;
        $this->showFifoPreview = false;
        $this->fifoPreview = [];
        $this->selectedBarangBatches = [];
        
        // Update FIFO preview after reset
        $this->updateFifoPreview();
    }

    public function render()
    {
        return view('livewire.edit-transaksi-barang-keluar', [
            'barangs' => Barang::where(function ($query) {
                $query->where('stok', '>', 0)
                    ->orWhereIn('id', collect($this->barangDetails)->pluck('barang_id')->filter());
            })
            ->orderBy('nama_barang')
            ->get(),
            'jenisKeluarOptions' => [
                'distribusi' => 'Distribusi',
                // 'retur' => 'Retur ke Supplier',
                // 'rusak' => 'Barang Rusak',
                // 'expired' => 'Barang Expired',
                // 'hilang' => 'Barang Hilang',
            ]
        ]);
    }
}