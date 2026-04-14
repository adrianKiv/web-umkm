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
        Schema::create('umkm_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengusul', 120);
            $table->string('email_pengusul', 160)->nullable();
            $table->string('nama_umkm', 120);
            $table->string('jam_buka', 60);
            $table->string('no_telfon', 25);
            $table->text('alamat_lengkap');
            $table->text('deskripsi');
            $table->integer('id_kategori');
            $table->decimal('latitude', 20, 15);
            $table->decimal('longitude', 20, 15);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('id_kategori')->references('id_kategori')->on('kategori');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umkm_submissions');
    }
};
