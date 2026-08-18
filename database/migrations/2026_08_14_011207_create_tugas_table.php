<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();

            // siapa yang memberi tugas (kepala bagian atau kepala BPS)
            $table->foreignId('dibuat_oleh')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // siapa yang menerima tugas (staf, atau kabag jika dari kepala BPS)
            $table->foreignId('ditugaskan_ke')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // dicatat juga bagian_id-nya, supaya query "tugas di bagian X" cepat
            // tanpa perlu join lewat users setiap saat
            $table->foreignId('bagian_id')
                  ->constrained('bagian')
                  ->cascadeOnDelete();

            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->date('tenggat')->nullable();

            // status: belum_dikerjakan, dikerjakan, selesai
            $table->string('status')->default('belum_dikerjakan');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};