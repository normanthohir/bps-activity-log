<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bagians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bagian');                 // contoh: "Seksi Statistik Sosial"
            $table->string('kode_bagian')->unique();        // contoh: "STAT-SOS"

            // kepala_bagian_id sengaja BELUM diberi foreign key di sini,
            // karena tabel users belum tentu punya data saat tabel ini dibuat.
            // Foreign key-nya ditambahkan lewat migration terpisah di bawah,
            // setelah kolom bagian_id pada users juga sudah ada.
            $table->foreignId('kepala_bagian_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bagian');
    }
};