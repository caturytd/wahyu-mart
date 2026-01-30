<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangBatch;
use App\Models\Barang;
use App\Models\Pemasok;

class BarangBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangBatch::with(['barang', 'pemasok']);
        
        // Filter berdasarkan barang
        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }
        
        // Filter berdasarkan pemasok
        if ($request->filled('pemasok_id')) {
            $query->where('pemasok_id', $request->pemasok_id);
        }
        
        // Filter berdasarkan tanggal masuk
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_masuk', '>=', $request->tanggal_dari);
        }
        
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_masuk', '<=', $request->tanggal_sampai);
        }
        
        // Filter berdasarkan status expired
        if ($request->filled('status_expired')) {
            $today = now()->toDateString();
            
            if ($request->status_expired === 'expired') {
                $query->where('expired_date', '<', $today);
            } elseif ($request->status_expired === 'akan_expired') {
                $query->where('expired_date', '>=', $today)
                      ->where('expired_date', '<=', now()->addDays(30)->toDateString());
            } elseif ($request->status_expired === 'normal') {
                $query->where(function($q) use ($today) {
                    $q->where('expired_date', '>', now()->addDays(30)->toDateString())
                      ->orWhereNull('expired_date');
                });
            }
        }
        
        // Filter berdasarkan status stok
        if ($request->filled('status_stok')) {
            if ($request->status_stok === 'habis') {
                $query->where('qty_tersisa', '<=', 0);
            } elseif ($request->status_stok === 'tersedia') {
                $query->where('qty_tersisa', '>', 0);
            }
        }
        
        // Urutkan berdasarkan tanggal masuk (FIFO)
        $batches = $query->orderBy('tanggal_masuk', 'asc')
                         ->orderBy('id', 'asc')
                         ->paginate(20);
        
        // Data untuk dropdown filter
        $barangs = Barang::select('id', 'nama_barang', 'kode_barang')
                         ->orderBy('nama_barang')
                         ->get();
        
        $pemasoks = Pemasok::select('id', 'nama_pemasok')
                          ->orderBy('nama_pemasok')
                          ->get();
        
        return view('admin.barang-batch.index', compact('batches', 'barangs', 'pemasoks'));
    }
    
    public function show($id)
    {
        $batch = BarangBatch::with(['barang', 'pemasok'])->findOrFail($id);
        
        return view('admin.barang-batch.show', compact('batch'));
    }
}