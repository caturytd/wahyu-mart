<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    public function index()
    {
        $pemasoks = Pemasok::all();
        return view('admin.pemasok.index', compact('pemasoks'));
    }

    public function create()
    {
        return view('admin.pemasok.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        Pemasok::create($request->all());

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil ditambahkan');
    }

    public function show(Pemasok $pemasok)
    {
        return view('admin.pemasok.edit', compact('pemasok'));
    }

    public function update(Request $request, Pemasok $pemasok)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        $pemasok->update($request->all());

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil diperbarui');
    }

    public function destroy(Pemasok $pemasok)
    {
        if ($pemasok->barangMasuk()->count() > 0) {
            return redirect()->route('pemasok.index')->with('error', 'Tidak dapat menghapus pemasok karena masih digunakan dalam transaksi barang masuk.');
        }

        $pemasok->delete();

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil dihapus');
}

}
