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
        Schema::create('menu', function (Blueprint $table) {
            $table->integer('id_menu')->primary()->autoIncrement();
            $table->string('nama_menu', 100);
            $table->decimal('harga_menu', 12, 2);
            $table->string('foto_menu', 255);
            $table->integer('id_umkm');
            $table->foreign('id_umkm')->references('id_umkm')->on('umkm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
