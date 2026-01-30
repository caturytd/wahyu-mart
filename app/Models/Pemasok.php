<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    protected $table = 'pemasok';

    protected $fillable = ['nama_pemasok', 'kontak', 'alamat'];

    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class);
    }

    public function barangBatch()
    {
        return $this->hasMany(BarangBatch::class);
    }
}
