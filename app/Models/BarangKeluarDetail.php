<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluarDetail extends Model
{
    protected $table = 'barang_keluar_detail';

    protected $fillable = [
        'barang_keluar_id', 'barang_id', 'qty',
        'nilai_satuan', 'total_nilai'
    ];

    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluar::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function keluarBatch()
    {
        return $this->hasMany(BarangKeluarBatch::class);
    }

    public function batchUsages()
    {
        return $this->hasMany(BarangKeluarBatch::class, 'barang_keluar_detail_id');
    }

    public function BatchDetails()
    {
        return $this->hasMany(BarangKeluarBatch::class);
    }

    public function batch()
    {
        return $this->hasMany(BarangKeluarBatch::class, 'barang_keluar_detail_id');
    }

}

