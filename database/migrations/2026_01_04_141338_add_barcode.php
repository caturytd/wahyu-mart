
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->string('barcode_path')->nullable()->after('no_transaksi');
        });
    }

    public function down()
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->dropColumn('barcode_path');
        });
    }
};