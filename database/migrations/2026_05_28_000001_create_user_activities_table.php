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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->bigIncrements('id_user_activities');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('id_session');
            $table->unsignedBigInteger('id_kategori');
            $table->string('interaction_type', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->index('id_user');
            $table->index('id_session');
            $table->index('id_kategori');

            $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
