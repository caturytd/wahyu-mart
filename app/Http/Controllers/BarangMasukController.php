<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use App\Models\BarangBatch;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use App\Models\PengaturanToko;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangMasuk::with('pemasok')->latest();

        if ($request->filled('pemasok')) {
            $query->where('pemasok_id', $request->pemasok);
        }

        $barangMasuks = $query->get();
        $pemasoks = Pemasok::all();

        return view('admin.barang-masuk.index', compact('barangMasuks', 'pemasoks'));
    }

    public function show($id)
    {
        $barangMasuk = BarangMasuk::with([
            'pemasok',
            'details.barang.kategori'
        ])->findOrFail($id);
        $pengaturanToko = PengaturanToko::first();

        return view('admin.barang-masuk.detail', compact(
            'barangMasuk',
            'pengaturanToko'
        ));
    }

public function destroy($id)
{
    DB::beginTransaction();

    try {
        $barangMasuk = BarangMasuk::with('details')->findOrFail($id);

        foreach ($barangMasuk->details as $detail) {
            $batch = BarangBatch::where('batch_code', $detail->batch_code)->first();

            if ($batch) {
                if ($batch->qty_tersisa < $batch->qty_awal) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Batch barang sudah digunakan, tidak bisa dihapus.');
                }
            }
        }

        // Hapus semua detail terlebih dahulu
        foreach ($barangMasuk->details as $detail) {
            $detail->delete();

            // Setelah detail dihapus, hapus batch-nya
            BarangBatch::where('batch_code', $detail->batch_code)->delete();
        }

        // Hapus header transaksi
        $barangMasuk->delete();

        DB::commit();
        return redirect()->route('barang-masuk.index')->with('success', 'Data barang masuk berhasil dihapus.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
    }
}

}
