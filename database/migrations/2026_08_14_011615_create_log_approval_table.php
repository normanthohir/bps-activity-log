<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_approval', function (Blueprint $table) {
            $table->id();

            $table->foreignId('laporan_id')
                  ->constrained('laporan_harian')
                  ->cascadeOnDelete();

            // yang melakukan aksi: kepala bagian atau kepala BPS
            $table->foreignId('approver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // aksi: disetujui, ditolak
            $table->string('aksi');

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_approval');
    }
};