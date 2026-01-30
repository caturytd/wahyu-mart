<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $laporanBarangKeluar = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->join('barang as b', 'bkd.barang_id', '=', 'b.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'bk.id',
                'bk.no_transaksi',
                'bk.tgl_keluar',
                'bk.jenis_keluar',
                'b.kode_barang',
                'b.nama_barang',
                'k.nama_kategori',
                'bkd.qty',
                'bkd.nilai_satuan',
                'bkd.total_nilai',
                'bk.keterangan'
            ])
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->orderBy('bk.tgl_keluar', 'desc')
            ->orderBy('bk.no_transaksi', 'desc')
            ->get();

        $ringkasanBarang = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->join('barang as b', 'bkd.barang_id', '=', 'b.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                'b.satuan',
                'k.nama_kategori',
                DB::raw('SUM(bkd.qty) as total_qty'),
                DB::raw('SUM(bkd.total_nilai) as total_nilai'),
                DB::raw('AVG(bkd.nilai_satuan) as rata_rata_nilai')
            ])
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->groupBy('b.id', 'b.kode_barang', 'b.nama_barang', 'b.satuan', 'k.nama_kategori')
            ->orderBy('total_qty', 'desc')
            ->get();

        $statistikJenisKeluar = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->select([
                'bk.jenis_keluar',
                DB::raw('COUNT(DISTINCT bk.id) as total_transaksi'),
                DB::raw('SUM(bkd.qty) as total_qty'),
                DB::raw('SUM(bkd.total_nilai) as total_nilai')
            ])
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->groupBy('bk.jenis_keluar')
            ->get();

        $totalKeseluruhan = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT bk.id) as total_transaksi,
                SUM(bkd.qty) as total_qty,
                SUM(bkd.total_nilai) as total_nilai
            ')
            ->first();

        return view('admin.laporan.barang-keluar.index', compact(
            'laporanBarangKeluar',
            'ringkasanBarang',
            'statistikJenisKeluar',
            'totalKeseluruhan',
            'startDate',
            'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $laporanBarangKeluar = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->join('barang as b', 'bkd.barang_id', '=', 'b.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'bk.id',
                'bk.no_transaksi',
                'bk.tgl_keluar',
                'bk.jenis_keluar',
                'b.kode_barang',
                'b.nama_barang',
                'k.nama_kategori',
                'bkd.qty',
                'bkd.nilai_satuan',
                'bkd.total_nilai',
                'bk.keterangan'
            ])
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->orderBy('bk.tgl_keluar', 'desc')
            ->get();

        $ringkasanBarang = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->join('barang as b', 'bkd.barang_id', '=', 'b.id')
            ->leftJoin('kategori_barang as k', 'b.kategori_id', '=', 'k.id')
            ->select([
                'b.kode_barang',
                'b.nama_barang',
                'b.satuan',
                'k.nama_kategori',
                DB::raw('SUM(bkd.qty) as total_qty'),
                DB::raw('SUM(bkd.total_nilai) as total_nilai')
            ])
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->groupBy('b.id', 'b.kode_barang', 'b.nama_barang', 'b.satuan', 'k.nama_kategori')
            ->orderBy('total_qty', 'desc')
            ->get();

        $totalKeseluruhan = DB::table('barang_keluar as bk')
            ->join('barang_keluar_detail as bkd', 'bk.id', '=', 'bkd.barang_keluar_id')
            ->whereBetween('bk.tgl_keluar', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT bk.id) as total_transaksi,
                SUM(bkd.qty) as total_qty,
                SUM(bkd.total_nilai) as total_nilai
            ')
            ->first();

        $pdf = Pdf::loadView('admin.laporan.barang-keluar.pdf', compact(
            'laporanBarangKeluar',
            'ringkasanBarang',
            'totalKeseluruhan',
            'startDate',
            'endDate'
        ));

        return $pdf->download('laporan-barang-keluar-' . $startDate . '-' . $endDate . '.pdf');
    }
}
