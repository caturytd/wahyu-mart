<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';

    protected $fillable = [
        'no_transaksi', 'tgl_masuk', 'pemasok_id',
        'total_qty', 'total_harga', 'status',
        'no_surat_jalan', 'received_by', 'keterangan'
    ];

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class);
    }

    public function details()
    {
        return $this->hasMany(BarangMasukDetail::class);
    }
}
