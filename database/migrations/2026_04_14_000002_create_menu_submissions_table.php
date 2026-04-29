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
        Schema::create('menu_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('umkm_submission_id')->nullable();
            $table->integer('id_umkm')->nullable();
            $table->string('nama_pengusul', 120)->nullable();
            $table->string('email_pengusul', 160)->nullable();
            $table->string('nama_menu', 100);
            $table->decimal('harga_menu', 12, 2);
            $table->string('foto_menu', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('umkm_submission_id')->references('id')->on('umkm_submissions')->cascadeOnDelete();
            $table->foreign('id_umkm')->references('id_umkm')->on('umkm')->cascadeOnDelete();
            $table->index(['status', 'created_at']);
            $table->index(['umkm_submission_id', 'status']);
            $table->index(['id_umkm', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_submissions');
    }
};
