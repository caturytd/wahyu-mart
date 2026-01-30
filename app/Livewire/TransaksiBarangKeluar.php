<?php

namespace App\Livewire;

use App\Models\Barang;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\BarangKeluar;
use App\Services\FifoService;
use App\Models\BarangKeluarDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiBarangKeluar extends Component
{
    public $tgl_keluar, $jenis_keluar = 'distribusi', $keterangan;
    public $barangDetails = [];
    public $showFifoPreview = false;
    public $fifoPreview = [];
    public $selectedBarangBatches = [];

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

    public function mount()
    {
        $this->tgl_keluar = now()->format('Y-m-d');
        $this->barangDetails[] = $this->newBarangDetailRow();
    }

    public function newBarangDetailRow()
    {
        return [
            'barang_id' => null,
            'nama_barang' => '',
            'satuan' => '',
            'qty' => 1,
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
            $this->barangDetails[$index]['stok_tersedia'] = $barang->stok ?? 0;

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

    private function generateNoTransaksi()
    {
        $prefix = 'BK';
        $date = now()->format('Ymd');
        
        $lastTransaction = BarangKeluar::where('no_transaksi', 'like', "{$prefix}-{$date}%")
            ->orderBy('no_transaksi', 'desc')
            ->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->no_transaksi, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "{$prefix}-{$date}-{$newNumber}";
    }

    public function validateStock()
    {
        $items = collect($this->barangDetails)
            ->filter(fn($item) => $item['barang_id'] && $item['qty'] > 0)
            ->map(fn($item) => [
                'barang_id' => $item['barang_id'],
                'qty' => $item['qty']
            ])
            ->toArray();

        if (empty($items)) {
            session()->flash('error', 'Tidak ada item yang akan dikeluarkan.');
            return false;
        }

        $validation = $this->fifoService->validateMultipleItems($items);
        
        if (!$validation['valid']) {
            $errors = [];
            foreach ($validation['items'] as $item) {
                if (!$item['is_sufficient']) {
                    $barang = Barang::find($item['barang_id']);
                    $errors[] = "{$barang->nama_barang}: Stok tidak mencukupi (Tersedia: {$item['qty_available']}, Diminta: {$item['qty_requested']})";
                }
            }
            
            session()->flash('error', 'Validasi stok gagal: ' . implode(', ', $errors));
            return false;
        }

        return true;
    }

    public function simpan()
{
    $this->validate();

    if (!$this->validateStock()) {
        return;
    }

    DB::beginTransaction();

    try {
        $no_transaksi = $this->generateNoTransaksi();

        // Calculate totals from FIFO preview
        $this->updateFifoPreview();
        $totalQty = collect($this->barangDetails)->sum('qty');
        $totalNilai = $this->fifoPreview['grand_total'] ?? 0;

        // Create barang keluar header
        $barangKeluar = BarangKeluar::create([
            'no_transaksi' => $no_transaksi,
            'tgl_keluar' => $this->tgl_keluar,
            'total_qty' => $totalQty,
            'total_nilai' => $totalNilai,
            'jenis_keluar' => $this->jenis_keluar,
            'keterangan' => $this->keterangan,
        ]);

        // Process each item
        foreach ($this->barangDetails as $detail) {
            if (!$detail['barang_id'] || $detail['qty'] <= 0) continue;

            // Get FIFO calculation
            $fifoData = $this->fifoService->calculateFifoAllocation(
                $detail['barang_id'], 
                $detail['qty']
            );

            // Create barang keluar detail
            $barangKeluarDetail = BarangKeluarDetail::create([
                'barang_keluar_id' => $barangKeluar->id,
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

        /*
        |--------------------------------------------------------------------------
        | GENERATE BARCODE (diambil dari function simpan terbaru)
        |--------------------------------------------------------------------------
        */
        $barcodeService = new \App\Services\BarcodeService();

        $additionalInfo = [
            ['label' => 'Tanggal', 'value' => date('d/m/Y', strtotime($this->tgl_keluar))],
            // ['label' => 'Jenis', 'value' => $this->jenisKeluarOptions[$this->jenis_keluar] ?? '-'],
            // ['label' => 'Total Item', 'value' => count($this->barangDetails) . ' jenis'],
        ];

        $barcodePath = $barcodeService->generateBarcodeWithLabel(
            $no_transaksi,
            $additionalInfo,
            'barang_keluar'
        );

        if ($barcodePath) {
            $barangKeluar->update([
                'barcode_path' => $barcodePath
            ]);
        }

        DB::commit();

        session()->flash('success', "Barang keluar berhasil disimpan dengan nomor transaksi: {$no_transaksi}");
        session()->flash('no_transaksi', $no_transaksi);
        session()->flash('show_barcode', true);

        return redirect()->route('barang-keluar.show', $barangKeluar->id);

    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
        Log::error('Error saving barang keluar: ' . $e->getMessage());
    }
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
        $this->reset(['jenis_keluar', 'keterangan', 'showFifoPreview']);
        $this->tgl_keluar = now()->format('Y-m-d');
        $this->jenis_keluar = 'distribusi';
        $this->barangDetails = [$this->newBarangDetailRow()];
        $this->fifoPreview = [];
        $this->selectedBarangBatches = [];
    }

    public function render()
    {
        return view('livewire.transaksi-barang-keluar', [
            'barangs' => Barang::where('stok', '>', 0)
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