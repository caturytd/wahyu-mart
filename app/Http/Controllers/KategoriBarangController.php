<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\Request;

class KategoriBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoriBarang = KategoriBarang::all();
        return view('admin.kategori-barang.index', compact('kategoriBarang'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.kategori-barang.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255'
        ]);

        KategoriBarang::create($request->all());

        return redirect()->route('kategori-barang.index')->with('success','Kategori barang berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(KategoriBarang $kategoriBarang)
    {
        return view('admin.kategori-barang.show', compact('kategoriBarang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriBarang $kategoriBarang)
    {
        return view('admin.kategori-barang.edit', compact('kategoriBarang'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriBarang $kategoriBarang)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255'
        ]);

        $kategoriBarang->update($request->all());

        return redirect()->route('kategori-barang.index')->with('success', 'Kategori barang berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriBarang $kategoriBarang)
    {
        if ($kategoriBarang->barang()->count() > 0) {
        return redirect()->route('kategori-barang.index')
            ->with('error', 'Tidak dapat menghapus kategori karena masih digunakan oleh barang.');
        }

        $kategoriBarang->delete();
        return redirect()->route('kategori-barang.index')->with('success', 'Kategori barang berhasil dihapus');
    }
}
