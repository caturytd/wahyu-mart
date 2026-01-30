<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\KategoriBarang;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with('kategori');

        if ($request->has('kategori') && !empty($request->kategori)) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('id', $request->kategori);
            });
        }

        $barangs = $query->get();
        $kategoris = KategoriBarang::all();

        return view('admin.barang.index', compact('barangs', 'kategoris'));
    }

    public function laporan(Request $request)
    {
        $query = Barang::with('kategori');

        if ($request->has('kategori') && !empty($request->kategori)) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('id', $request->kategori);
            });
        }

        $barang = $query->get();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.barang.laporan', compact('barang'));

        return $pdf->download('laporan_barang.pdf');
    }

    public function create()
    {
        $lastBarang = Barang::latest()->first();
        $newCode = 'TS-001';

        if ($lastBarang && preg_match('/^TS-(\d+)$/', $lastBarang->kode_barang, $matches)) {
            $lastCode = (int) $matches[1];
            $newCode = 'TS-' . str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
        }

        $kategori = KategoriBarang::all();

        return view('admin.barang.create', compact('newCode', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required',
            'nama_barang' => 'required',
            'satuan' => 'required',
            'kategori_id' => 'required|exists:kategori_barang,id',
            'stok' => 'required|integer',
            'min_stok' => 'required|integer',
        ]);

        Barang::create($request->all());

        return redirect()->route('barang.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        $kategori = KategoriBarang::all();
        return view('admin.barang.edit', compact('barang', 'kategori'));
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'kode_barang' => 'required|unique:barang,kode_barang,' . $barang->id,
            'nama_barang' => 'required|string',
            'min_stok' => 'required|integer',
            'satuan' => 'required',
            'kategori_id' => 'required|exists:kategori_barang,id',
            'stok' => 'required|integer',
        ]);

        $barang->update($request->all());

        return redirect()->route('barang.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        if ($barang->barangMasukDetails()->exists() || $barang->barangKeluarDetails()->exists()) {
            return redirect()->route('barang.index')
                ->with('error', 'Tidak dapat menghapus barang karena sudah digunakan dalam transaksi.');
        }

        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }
}
