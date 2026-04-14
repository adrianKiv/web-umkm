<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('umkm', function (Blueprint $table) {
            $table->integer('id_umkm')->primary()->autoIncrement();
            $table->string('nama_umkm', 100);
            $table->string('slug_umkm', 100)->unique();
            $table->string('jam_buka', 50);
            $table->string('no_telfon', 20);
            $table->text('alamat_lengkap');
            $table->text('deskripsi');
            $table->string('foto_umkm', 255);
            $table->integer('id_lokasi');
            $table->integer('id_kategori');
            $table->foreign('id_lokasi')->references('id_lokasi')->on('lokasi');
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umkm');
    }
};
