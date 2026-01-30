<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Detail transaksi barang masuk
        $laporanBarangMasuk = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->join('barang as b', 'bmd.barang_id', '=', 'b.id')
            ->join('pemasok as p', 'bm.pemasok_id', '=', 'p.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'bm.id',
                'bm.no_transaksi',
                'bm.tgl_masuk',
                'bm.status',
                'bm.no_surat_jalan',
                'bm.received_by',
                'p.nama_pemasok',
                'b.kode_barang',
                'b.nama_barang',
                'k.nama_kategori',
                'bmd.qty',
                'bmd.harga_satuan',
                'bmd.total_harga',
                'bmd.batch_code',
                'bmd.expired_date',
                'bm.keterangan'
            ])
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->orderBy('bm.tgl_masuk', 'desc')
            ->orderBy('bm.no_transaksi', 'desc')
            ->get();

        // Ringkasan per barang
        $ringkasanBarang = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->join('barang as b', 'bmd.barang_id', '=', 'b.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                'b.satuan',
                'k.nama_kategori',
                DB::raw('SUM(bmd.qty) as total_qty'),
                DB::raw('SUM(bmd.total_harga) as total_harga'),
                DB::raw('AVG(bmd.harga_satuan) as rata_rata_harga')
            ])
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->groupBy('b.id', 'b.kode_barang', 'b.nama_barang', 'b.satuan', 'k.nama_kategori')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Statistik per pemasok
        $statistikPemasok = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->join('pemasok as p', 'bm.pemasok_id', '=', 'p.id')
            ->select([
                'p.nama_pemasok',
                'p.kontak',
                DB::raw('COUNT(DISTINCT bm.id) as total_transaksi'),
                DB::raw('SUM(bmd.qty) as total_qty'),
                DB::raw('SUM(bmd.total_harga) as total_harga')
            ])
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->groupBy('p.id', 'p.nama_pemasok', 'p.kontak')
            ->orderBy('total_harga', 'desc')
            ->get();

        // Statistik per status
        $statistikStatus = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->select([
                'bm.status',
                DB::raw('COUNT(DISTINCT bm.id) as total_transaksi'),
                DB::raw('SUM(bmd.qty) as total_qty'),
                DB::raw('SUM(bmd.total_harga) as total_harga')
            ])
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->groupBy('bm.status')
            ->get();

        // Total keseluruhan
        $totalKeseluruhan = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT bm.id) as total_transaksi,
                SUM(bmd.qty) as total_qty,
                SUM(bmd.total_harga) as total_harga
            ')
            ->first();

        return view('admin.laporan.barang-masuk.index', compact(
            'laporanBarangMasuk',
            'ringkasanBarang',
            'statistikPemasok',
            'statistikStatus',
            'totalKeseluruhan',
            'startDate',
            'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Detail transaksi barang masuk
        $laporanBarangMasuk = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->join('barang as b', 'bmd.barang_id', '=', 'b.id')
            ->join('pemasok as p', 'bm.pemasok_id', '=', 'p.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'bm.id',
                'bm.no_transaksi',
                'bm.tgl_masuk',
                'bm.status',
                'p.nama_pemasok',
                'b.kode_barang',
                'b.nama_barang',
                'k.nama_kategori',
                'bmd.qty',
                'bmd.harga_satuan',
                'bmd.total_harga',
                'bmd.batch_code',
                'bmd.expired_date'
            ])
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->orderBy('bm.tgl_masuk', 'desc')
            ->get();

        // Ringkasan per barang
        $ringkasanBarang = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->join('barang as b', 'bmd.barang_id', '=', 'b.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                'b.satuan',
                'k.nama_kategori',
                DB::raw('SUM(bmd.qty) as total_qty'),
                DB::raw('SUM(bmd.total_harga) as total_harga'),
                DB::raw('AVG(bmd.harga_satuan) as rata_rata_harga')

            ])
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->groupBy('b.id', 'b.kode_barang', 'b.nama_barang', 'b.satuan', 'k.nama_kategori')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Total keseluruhan
        $totalKeseluruhan = DB::table('barang_masuk as bm')
            ->join('barang_masuk_detail as bmd', 'bm.id', '=', 'bmd.barang_masuk_id')
            ->whereBetween('bm.tgl_masuk', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT bm.id) as total_transaksi,
                SUM(bmd.qty) as total_qty,
                SUM(bmd.total_harga) as total_harga
            ')
            ->first();

        $pdf = Pdf::loadView('admin.laporan.barang-masuk.pdf', compact(
            'laporanBarangMasuk',
            'ringkasanBarang',
            'totalKeseluruhan',
            'startDate',
            'endDate'
        ));

        return $pdf->download('laporan-barang-masuk-' . $startDate . '-' . $endDate . '.pdf');
    }
}