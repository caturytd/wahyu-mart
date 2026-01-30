<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluarBatch extends Model
{
    protected $table = 'barang_keluar_batch';
    protected $fillable = [
        'barang_keluar_detail_id', 'barang_batch_id',
        'qty_digunakan', 'nilai_satuan', 'total_nilai'
    ];

    public function barangBatch()
    {
        return $this->belongsTo(BarangBatch::class);
    }

    public function batchDetail()
    {
        return $this->belongsTo(BarangBatch::class);
    }

    public function barangKeluarDetail()
    {
        return $this->belongsTo(BarangKeluarDetail::class);
    }
}
