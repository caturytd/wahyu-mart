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

class TransaksiBarangMasuk extends Component
{
    public $tgl_masuk, $pemasok_id, $no_surat_jalan, $received_by, $keterangan;
    public $barangDetails = [];
    public $showPreview = false;
    
    protected $messages = [
        'barangDetails.*.barang_id.required' => 'Barang harus dipilih',
        'barangDetails.*.qty.min' => 'Qty minimal 1',
        'barangDetails.*.qty.max' => 'Qty maksimal 999,999',
        'barangDetails.*.harga_satuan.min' => 'Harga tidak boleh negatif',
        'barangDetails.*.expired_date.after' => 'Tanggal expired harus setelah hari ini',
        'tgl_masuk.before_or_equal' => 'Tanggal masuk tidak boleh di masa depan',
    ];

    public function mount()
    {
        $this->tgl_masuk = now()->format('Y-m-d');
        $this->barangDetails[] = $this->newBarangDetailRow();
    }

    public function newBarangDetailRow()
    {
        return [
            'barang_id' => null,
            'nama_barang' => '', // Untuk display
            'qty' => 1,
            'harga_satuan' => 0,
            'total_harga' => 0,
            'expired_date' => null,
            'satuan' => '' // Untuk display satuan
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
        }
    }

    public function updatedBarangDetails($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        // Handle harga_satuan changes
        if ($field === 'harga_satuan') {
            // Pastikan nilai adalah numeric
            $this->barangDetails[$index]['harga_satuan'] = (int) str_replace(['.', ','], '', $value);
            $this->calculateTotal($index);
        }

        // Handle qty changes - INI YANG KURANG!
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
            // Recalculate total when barang changes (in case qty and harga already set)
            $this->calculateTotal($index);
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
        return collect($this->barangDetails)->sum('qty');
    }

    public function getTotalHargaProperty()
    {
        return collect($this->barangDetails)->sum('total_harga');
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

    private function generateNoTransaksi()
    {
        $prefix = 'BM';
        $date = now()->format('Ymd');
        
        // Get last transaction number for today
        $lastTransaction = BarangMasuk::where('no_transaksi', 'like', "{$prefix}-{$date}%")
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

    public function simpan()
    {
        $this->validate([
            'tgl_masuk' => 'required|date|before_or_equal:today',
            'pemasok_id' => 'required|exists:pemasok,id',
            'barangDetails' => 'required|array|min:1',
            'barangDetails.*.barang_id' => 'required|exists:barang,id',
            'barangDetails.*.qty' => 'required|numeric|min:1|max:999999',
            'barangDetails.*.harga_satuan' => 'required|numeric|min:0',
            'barangDetails.*.expired_date' => 'nullable|date|after:today',
        ], $this->messages);

        // Check for duplicate barang in same transaction
        $barangIds = collect($this->barangDetails)->pluck('barang_id')->filter();
        if ($barangIds->count() !== $barangIds->unique()->count()) {
            session()->flash('error', 'Barang yang sama tidak boleh diinput lebih dari satu kali dalam satu transaksi.');
            return;
        }

        DB::beginTransaction();

        try {
            $no_transaksi = $this->generateNoTransaksi();

            $barangMasuk = BarangMasuk::create([
                'no_transaksi' => $no_transaksi,
                'tgl_masuk' => $this->tgl_masuk,
                'pemasok_id' => $this->pemasok_id,
                'total_qty' => $this->totalQty,
                'total_harga' => $this->totalHarga,
                'status' => 'received',
                'no_surat_jalan' => $this->no_surat_jalan,
                'received_by' => $this->received_by ?: auth()->user()->nama,
                'keterangan' => $this->keterangan,
            ]);

            foreach ($this->barangDetails as $detail) {
                if (!$detail['barang_id']) continue;

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
                    'no_transaksi_masuk' => $no_transaksi,
                ]);

                // Create detail record
                BarangMasukDetail::create([
                    'barang_masuk_id' => $barangMasuk->id,
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
                
                // Optional: Update last purchase price
                $barang->update(['harga_beli_terakhir' => $detail['harga_satuan']]);
            }

            DB::commit();

            session()->flash('success', "Barang masuk berhasil disimpan dengan nomor transaksi: {$no_transaksi}");
           return redirect()->route('barang-masuk.show', $barangMasuk->id);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
            Log::error('Error saving barang masuk: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['pemasok_id', 'no_surat_jalan', 'received_by', 'keterangan']);
        $this->tgl_masuk = now()->format('Y-m-d');
        $this->barangDetails = [$this->newBarangDetailRow()];
        $this->showPreview = false;
    }

    public function render()
    {
        return view('livewire.transaksi-barang-masuk', [
            'pemasoks' => Pemasok::orderBy('nama_pemasok')->get(),
            'barangs' => Barang::all(),
        ]);
    }
}