<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanToko extends Model
{
    protected $table = 'pengaturan_toko';
    protected $fillable = [
        'nama_toko', 'desa', 'kecamatan', 'kabupaten'
    ];
}
