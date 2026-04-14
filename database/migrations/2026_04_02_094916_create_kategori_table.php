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
        Schema::create('kategori', function (Blueprint $table) {
            $table->integer('id_kategori')->primary()->autoIncrement();
            $table->string('nama_kategori', 50);
            $table->string('slug_kategori', 50)->unique();
            $table->integer('id_kelompok');
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
