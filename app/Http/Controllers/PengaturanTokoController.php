<?php

namespace App\Http\Controllers;

use App\Models\PengaturanToko;
use Illuminate\Http\Request;

class PengaturanTokoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pengaturanToko = PengaturanToko::first();
        return view('admin.pengaturan.pengaturan-toko.index', compact('pengaturanToko'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_toko' => 'required',
            'desa' => 'required',
            'kecamatan' => 'required',
            'kabupaten' => 'required',
        ]);

        $pengaturan = PengaturanToko::first();

        if (!$pengaturan) {
            // Jika belum ada data, maka create
            PengaturanToko::create($request->all());
            return redirect()->back()->with('success', 'Pengaturan toko berhasil disimpan.');
        } else {
            // Jika sudah ada data, maka update
            $pengaturan->update($request->all());
            return redirect()->back()->with('success', 'Pengaturan toko berhasil diperbarui.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PengaturanToko $pengaturanToko)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PengaturanToko $pengaturanToko)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PengaturanToko $pengaturanToko)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PengaturanToko $pengaturanToko)
    {
        //
    }
}
