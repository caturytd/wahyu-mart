<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $fillable = ['nama_barang', 'kategori_id', 'kode_barang', 'stok', 'min_stok', 'satuan', 'gambar'];

    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }

    public function batch()
    {
        return $this->hasMany(BarangBatch::class);
    }

    public function barangMasukDetails()
    {
        return $this->hasMany(BarangMasukDetail::class);
    }

    public function barangKeluarDetails()
    {
        return $this->hasMany(BarangKeluarDetail::class);
    }

}
