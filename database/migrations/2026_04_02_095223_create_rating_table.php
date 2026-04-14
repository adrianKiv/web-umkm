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
        Schema::create('rating', function (Blueprint $table) {
            $table->integer('id_rating')->primary()->autoIncrement();
            $table->string('nama_pengulas', 50);
            $table->tinyInteger('nilai_rating')->unsigned();
            $table->text('komentar');
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
        Schema::dropIfExists('rating');
    }
};
