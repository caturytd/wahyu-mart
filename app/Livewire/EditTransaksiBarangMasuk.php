<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Barang;
use App\Models\Pemasok;
use Livewire\Component;
use App\Models\BarangBatch;
use App\Models\BarangMasuk;
use Illuminate\Support\Str;
use App\Models\BarangMasukDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditTransaksiBarangMasuk extends Component
{
    public $barangMasukId;
    public $barangMasuk;
    public $tgl_masuk, $pemasok_id, $no_surat_jalan, $received_by, $keterangan;
    public $barangDetails = [];
    public $showPreview = false;
    public $originalDetails = []; // Untuk tracking perubahan
    
    protected $messages = [
        'barangDetails.*.barang_id.required' => 'Barang harus dipilih',
        'barangDetails.*.qty.min' => 'Qty minimal 1',
        'barangDetails.*.qty.max' => 'Qty maksimal 999,999',
        'barangDetails.*.harga_satuan.min' => 'Harga tidak boleh negatif',
        'barangDetails.*.expired_date.after' => 'Tanggal expired harus setelah hari ini',
        'tgl_masuk.before_or_equal' => 'Tanggal masuk tidak boleh di masa depan',
    ];

    public function mount($id)
    {
        $this->barangMasukId = $id;
        $this->loadBarangMasuk();
    }

    private function loadBarangMasuk()
    {
        $this->barangMasuk = BarangMasuk::with(['details.barang', 'pemasok'])
            ->findOrFail($this->barangMasukId);

        // Load header data
        $this->tgl_masuk = $this->barangMasuk->tgl_masuk;
        $this->pemasok_id = $this->barangMasuk->pemasok_id;
        $this->no_surat_jalan = $this->barangMasuk->no_surat_jalan;
        $this->received_by = $this->barangMasuk->received_by;
        $this->keterangan = $this->barangMasuk->keterangan;

        // Load detail data
        $this->barangDetails = [];
        $this->originalDetails = [];

        foreach ($this->barangMasuk->details as $detail) {
            $detailData = [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $detail->barang->nama_barang,
                'qty' => $detail->qty,
                'harga_satuan' => $detail->harga_satuan,
                'total_harga' => $detail->total_harga,
                'expired_date' => $detail->expired_date,
                'satuan' => $detail->barang->satuan ?? 'pcs',
                'batch_code' => $detail->batch_code,
                'is_existing' => true // Flag untuk identifikasi data existing
            ];
            
            $this->barangDetails[] = $detailData;
            $this->originalDetails[] = $detailData; // Backup original data
        }

        // Jika tidak ada detail, tambahkan row kosong
        if (empty($this->barangDetails)) {
            $this->barangDetails[] = $this->newBarangDetailRow();
        }
    }

    public function newBarangDetailRow()
    {
        return [
            'id' => null,
            'barang_id' => null,
            'nama_barang' => '',
            'qty' => 1,
            'harga_satuan' => 0,
            'total_harga' => 0,
            'expired_date' => null,
            'satuan' => '',
            'batch_code' => null,
            'is_existing' => false
        ];
    }

    public function addRow()
    {
        $this->barangDetails[] = $this->newBarangDetailRow();
    }

    public function removeRow($index)
    {
        if (count($this->barangDetails) > 1) {
            // Jika ini adalah detail yang sudah ada di database, cek apakah batch sudah digunakan
            if (isset($this->barangDetails[$index]['id']) && isset($this->barangDetails[$index]['batch_code'])) {
                $batch = BarangBatch::where('batch_code', $this->barangDetails[$index]['batch_code'])->first();
                if ($batch && $batch->qty_tersisa < $batch->qty_awal) {
                    session()->flash('error', 'Batch pada barang "' . $this->barangDetails[$index]['nama_barang'] . '" sudah digunakan, tidak bisa dihapus.');
                    return;
                }
                // Mark untuk deletion
                $this->barangDetails[$index]['_delete'] = true;
            } else {
                // Jika ini adalah row baru, hapus langsung
                unset($this->barangDetails[$index]);
                $this->barangDetails = array_values($this->barangDetails);
            }
        }
    }

    public function updatedBarangDetails($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'harga_satuan') {
            // Pastikan nilai adalah numeric
            $this->barangDetails[$index]['harga_satuan'] = (int) str_replace(['.', ','], '', $value);
            $this->calculateTotal($index);
        }

        if ($field === 'qty') {
            $this->calculateTotal($index);
        }

        // Auto fill barang info when barang_id selected
        if ($field === 'barang_id' && $value) {
            $barang = Barang::find($value);
            if ($barang) {
                $this->barangDetails[$index]['nama_barang'] = $barang->nama_barang;
                $this->barangDetails[$index]['satuan'] = $barang->satuan ?? 'pcs';
            }
        }
    }

    private function calculateTotal($index)
    {
        $qty = $this->barangDetails[$index]['qty'] ?? 0;
        $harga = $this->barangDetails[$index]['harga_satuan'] ?? 0;
        $this->barangDetails[$index]['total_harga'] = $qty * $harga;
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    public function getTotalQtyProperty()
    {
        return collect($this->barangDetails)
            ->reject(function ($detail) {
                return isset($detail['_delete']) && $detail['_delete'];
            })
            ->sum('qty');
    }

    public function getTotalHargaProperty()
    {
        return collect($this->barangDetails)
            ->reject(function ($detail) {
                return isset($detail['_delete']) && $detail['_delete'];
            })
            ->sum('total_harga');
    }

    private function generateBatchCode($barangId, $tanggalMasuk)
    {
        $prefix = 'BT';
        $date = Carbon::parse($tanggalMasuk)->format('ymd');
        
        // Ensure uniqueness
        do {
            $random = Str::random(2);
            $batchCode = "{$prefix}{$date}{$random}";
        } while (BarangBatch::where('batch_code', $batchCode)->exists());
        
        return $batchCode;
    }

    public function update()
    {
        // Validasi batch yang akan dihapus terlebih dahulu
        foreach ($this->barangDetails as $detail) {
            if (isset($detail['_delete']) && $detail['_delete'] && isset($detail['id']) && isset($detail['batch_code'])) {
                $batch = BarangBatch::where('batch_code', $detail['batch_code'])->first();
                if ($batch && $batch->qty_tersisa < $batch->qty_awal) {
                    session()->flash('error', 'Batch barang "' . $detail['nama_barang'] . '" sudah digunakan, tidak bisa dihapus.');
                    return;
                }
            }
        }

        // Filter out deleted items for validation
        $activeDetails = collect($this->barangDetails)
            ->reject(function ($detail) {
                return isset($detail['_delete']) && $detail['_delete'];
            })
            ->toArray();

        $this->validate([
            'tgl_masuk' => 'required|date|before_or_equal:today',
            'pemasok_id' => 'required|exists:pemasok,id',
            'barangDetails' => 'required|array|min:1',
            'barangDetails.*.barang_id' => 'required|exists:barang,id',
            'barangDetails.*.qty' => 'required|numeric|min:1|max:999999',
            'barangDetails.*.harga_satuan' => 'required|numeric|min:0',
            'barangDetails.*.expired_date' => 'nullable|date|after:today',
        ], $this->messages);

        // Check for duplicate barang in same transaction (excluding deleted items)
        $barangIds = collect($activeDetails)->pluck('barang_id')->filter();
        if ($barangIds->count() !== $barangIds->unique()->count()) {
            session()->flash('error', 'Barang yang sama tidak boleh diinput lebih dari satu kali dalam satu transaksi.');
            return;
        }

        DB::beginTransaction();

        try {
            // Update header
            $this->barangMasuk->update([
                'tgl_masuk' => $this->tgl_masuk,
                'pemasok_id' => $this->pemasok_id,
                'total_qty' => $this->totalQty,
                'total_harga' => $this->totalHarga,
                'no_surat_jalan' => $this->no_surat_jalan,
                'received_by' => $this->received_by ?: auth()->user()->nama,
                'keterangan' => $this->keterangan,
            ]);

            // Process detail changes
            foreach ($this->barangDetails as $detail) {
                // Skip if marked for deletion but process deletion later
                if (isset($detail['_delete']) && $detail['_delete'] && isset($detail['id'])) {
                    continue;
                }

                // Skip if marked for deletion and no ID (new item that was deleted)
                if (isset($detail['_delete']) && $detail['_delete'] && !isset($detail['id'])) {
                    continue;
                }

                if (!$detail['barang_id']) continue;

                if ($detail['is_existing'] && isset($detail['id'])) {
                    // Update existing detail
                    $existingDetail = BarangMasukDetail::find($detail['id']);
                    if ($existingDetail) {
                        // Calculate stock difference
                        $qtyDiff = $detail['qty'] - $existingDetail->qty;
                        
                        // Update detail
                        $existingDetail->update([
                            'barang_id' => $detail['barang_id'],
                            'qty' => $detail['qty'],
                            'harga_satuan' => $detail['harga_satuan'],
                            'total_harga' => $detail['qty'] * $detail['harga_satuan'],
                            'expired_date' => $detail['expired_date'],
                        ]);

                        // Update batch
                        $batch = BarangBatch::where('batch_code', $detail['batch_code'])->first();
                        if ($batch) {
                            $batch->update([
                                'expired_date' => $detail['expired_date'],
                                'qty_awal' => $detail['qty'],
                                'qty_tersisa' => $batch->qty_tersisa + $qtyDiff,
                                'harga_satuan' => $detail['harga_satuan'],
                                'pemasok_id' => $this->pemasok_id,
                            ]);
                        }

                        // Update stock in barang table
                        $barang = Barang::find($detail['barang_id']);
                        if ($qtyDiff != 0) {
                            if ($qtyDiff > 0) {
                                $barang->increment('stok', $qtyDiff);
                            } else {
                                $barang->decrement('stok', abs($qtyDiff));
                            }
                        }
                        
                        // Update last purchase price
                        $barang->update(['harga_beli_terakhir' => $detail['harga_satuan']]);
                    }
                } else {
                    // Create new detail
                    $batchCode = $this->generateBatchCode($detail['barang_id'], $this->tgl_masuk);

                    // Create batch record
                    BarangBatch::create([
                        'barang_id' => $detail['barang_id'],
                        'batch_code' => $batchCode,
                        'tanggal_masuk' => $this->tgl_masuk,
                        'expired_date' => $detail['expired_date'],
                        'qty_awal' => $detail['qty'],
                        'qty_tersisa' => $detail['qty'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'pemasok_id' => $this->pemasok_id,
                        'no_transaksi_masuk' => $this->barangMasuk->no_transaksi,
                    ]);

                    // Create detail record
                    BarangMasukDetail::create([
                        'barang_masuk_id' => $this->barangMasuk->id,
                        'barang_id' => $detail['barang_id'],
                        'batch_code' => $batchCode,
                        'qty' => $detail['qty'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'total_harga' => $detail['qty'] * $detail['harga_satuan'],
                        'expired_date' => $detail['expired_date'],
                    ]);

                    // Update stock in barang table
                    $barang = Barang::find($detail['barang_id']);
                    $barang->increment('stok', $detail['qty']);
                    
                    // Update last purchase price
                    $barang->update(['harga_beli_terakhir' => $detail['harga_satuan']]);
                }
            }

            // Process deletions
            foreach ($this->barangDetails as $detail) {
                if (isset($detail['_delete']) && $detail['_delete'] && isset($detail['id'])) {
                    $existingDetail = BarangMasukDetail::find($detail['id']);
                    if ($existingDetail) {
                        // Double check batch usage before deletion
                        $batch = BarangBatch::where('batch_code', $existingDetail->batch_code)->first();
                        if ($batch) {
                            if ($batch->qty_tersisa < $batch->qty_awal) {
                                DB::rollBack();
                                session()->flash('error', 'Batch barang "' . $detail['nama_barang'] . '" sudah digunakan, tidak bisa dihapus.');
                                return;
                            }
                        }

                        // Decrease stock
                        $barang = Barang::find($existingDetail->barang_id);
                        $barang->decrement('stok', $existingDetail->qty);

                        // Delete batch (if no other references)
                        if ($batch && $batch->qty_tersisa == $batch->qty_awal) {
                            $batch->delete();
                        }

                        // Delete detail
                        $existingDetail->delete();
                    }
                }
            }

            DB::commit();

            session()->flash('success', 'Transaksi barang masuk berhasil diperbarui.');
            return redirect()->route('barang-masuk.show', $this->barangMasukId);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
            Log::error('Error updating barang masuk: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->loadBarangMasuk(); // Reload original data
        $this->showPreview = false;
        session()->flash('success', 'Form berhasil direset ke data asli.');
    }

    public function render()
    {
        return view('livewire.edit-transaksi-barang-masuk', [
            'pemasoks' => Pemasok::orderBy('nama_pemasok')->get(),
            'barangs' => Barang::all(),
        ]);
    }
}