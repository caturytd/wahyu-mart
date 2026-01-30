<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';
    protected $fillable = [
        'no_transaksi', 'tgl_keluar', 'total_qty',
        'total_nilai', 'jenis_keluar', 'keterangan', 'barcode_path'
    ];

    public function details()
    {
        return $this->hasMany(BarangKeluarDetail::class);
    }

       public function barangDetails()
    {
        return $this->hasMany(BarangKeluarDetail::class);
    }

    
}
