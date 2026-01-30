<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasukDetail extends Model
{

    protected $table = 'barang_masuk_detail';

    protected $fillable = [
        'barang_masuk_id', 'barang_id', 'batch_code',
        'qty', 'harga_satuan', 'total_harga', 'expired_date'
    ];

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function batch()
    {
        return $this->belongsTo(BarangBatch::class, 'batch_code', 'batch_code');
    }
}

