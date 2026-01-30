<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangBatch extends Model
{
    protected $table = 'barang_batch';
    protected $fillable = [
        'barang_id', 'batch_code', 'tanggal_masuk', 'expired_date',
        'qty_awal', 'qty_tersisa', 'harga_satuan', 'pemasok_id', 'no_transaksi_masuk'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class);
    }


    public function keluarBatch()
    {
        return $this->hasMany(BarangKeluarBatch::class);
    }

    protected $casts = [
    'tanggal_masuk' => 'datetime',
    'expired_date' => 'datetime',
    ];

}