<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
    {
        Schema::create('kategori_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->timestamps();
        });

        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->foreignId('kategori_id')->constrained('kategori_barang')->cascadeOnDelete();
            $table->string('kode_barang')->unique();
            $table->integer('stok')->default(0); // Total stok (sum dari semua batch)
            $table->integer('min_stok');
            $table->string('satuan')->default('pcs'); // Unit (pcs, kg, liter, box, karton, dll)
            $table->timestamps();
        });

        Schema::create('pemasok', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pemasok');
            $table->string('kontak');
            $table->text('alamat')->nullable();
            $table->timestamps();
        });


        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tgl_masuk');
            $table->foreignId('pemasok_id')->constrained('pemasok');
            $table->integer('total_qty');
            $table->decimal('total_harga', 15, 2);
            $table->string('no_surat_jalan')->nullable(); // Nomor surat jalan
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('barang_masuk_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_masuk_id')->constrained('barang_masuk')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang');
            $table->string('batch_code'); // Link ke batch
            $table->integer('qty');
            $table->decimal('harga_satuan', 10, 2); // Harga per unit
            $table->decimal('total_harga', 15, 2);
            $table->timestamps();
            
            $table->foreign('batch_code')->references('batch_code')->on('barang_batch');
        });

        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tgl_keluar');
            $table->integer('total_qty');
            $table->decimal('total_nilai', 15, 2); // Total nilai barang keluar
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('barang_keluar_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_keluar_id')->constrained('barang_keluar')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang');
            $table->integer('qty');
            $table->decimal('nilai_satuan', 10, 2); // Nilai per unit (berdasarkan harga batch)
            $table->decimal('total_nilai', 15, 2);
            $table->timestamps();
        });

        // Tabel untuk tracking penggunaan batch saat barang keluar (FIFO tracking)
        Schema::create('barang_keluar_batch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_keluar_detail_id')->constrained('barang_keluar_detail')->cascadeOnDelete();
            $table->integer('qty_digunakan'); // Berapa qty yang diambil dari batch ini
            $table->decimal('nilai_satuan', 10, 2); // Nilai dari batch ini
            $table->decimal('total_nilai', 15, 2);
            $table->timestamps();
        });

  
    }

    public function down()
    {
        Schema::dropIfExists('barang_keluar_batch');
        Schema::dropIfExists('barang_keluar_detail');
        Schema::dropIfExists('barang_keluar');
        Schema::dropIfExists('tujuan_kirim');
        Schema::dropIfExists('barang_masuk_detail');
        Schema::dropIfExists('barang_masuk');
        Schema::dropIfExists('barang_batch');
        Schema::dropIfExists('pemasok');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('kategori_barang');
    }
};
