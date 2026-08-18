<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();

            // pemilik laporan (staf yang mengisi, atau kabag saat lapor pribadi)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // opsional: laporan ini terkait tugas yang mana (boleh kosong
            // kalau laporan sifatnya kegiatan mandiri, bukan dari penugasan)
            $table->foreignId('tugas_id')
                  ->nullable()
                  ->constrained('tugas')
                  ->nullOnDelete();

            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('uraian');
            $table->text('output')->nullable();

            // lokasi: kantor, lapangan, dinas_luar
            $table->string('lokasi')->default('kantor');

            $table->string('file_lampiran')->nullable();

            // status: draft, menunggu, disetujui, dikembalikan
            $table->string('status')->default('draft');

            // siapa yang menyetujui/menolak (diisi saat approval terjadi)
            $table->foreignId('disetujui_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('disetujui_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};