<?php

namespace App\Http\Controllers;

use App\Models\BarangBatch;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use App\Models\PengaturanToko;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
     public function index()
    {
        // Pastikan Anda mengambil data barang keluar berdasarkan transaksi
        $barangKeluar = BarangKeluar::with('details')  // Pastikan eager load relasi
                                    ->latest()
                                    ->get()
                                    ->groupBy('no_transaksi'); // Kelompokkan berdasarkan no_transaksi
    
        return view('admin.barang-keluar.index', compact('barangKeluar'));
    }

    public function show($id)
    {
        $barangKeluar = BarangKeluar::findOrFail($id);
        $pengaturanToko = PengaturanToko::first();

        return view('admin.barang-keluar.detail', compact('barangKeluar', 'pengaturanToko'));
    }

 public function destroy($id)
{
    DB::beginTransaction();

    try {
        // Ambil transaksi lengkap beserta relasi
        $barangKeluar = BarangKeluar::with(['details.batchUsages'])->findOrFail($id);

        // Loop setiap detail transaksi
        foreach ($barangKeluar->details as $detail) {

            // Loop penggunaan batch pada setiap detail
            foreach ($detail->batchUsages as $batchUsage) {
                // Kembalikan qty ke batch
                $batch = BarangBatch::find($batchUsage->barang_batch_id);

                if ($batch) {
                    $batch->qty_tersisa += $batchUsage->qty_digunakan;
                    $batch->save();
                }

                // Hapus catatan penggunaan batch
                $batchUsage->delete();
            }

            // Hapus detail barang keluar
            $detail->delete();
        }

        // Hapus transaksi barang keluar
        $barangKeluar->delete();

        DB::commit();
        return redirect()->route('barang-keluar.index')->with('success', 'Data barang keluar berhasil dihapus.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
    }
}

}
