<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel bagian dulu, TANPA foreign key kepala_bagian_id
        Schema::create('bagian', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bagian');
            $table->string('kode_bagian')->unique();
            $table->foreignId('kepala_bagian_id')->nullable();
            $table->timestamps();
        });

        // 2. Tambah kolom baru ke users, termasuk foreign key ke bagian
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->unique()->nullable()->after('name');
            $table->string('role')->default('staf')->after('nip');

            $table->foreignId('bagian_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('bagian')
                  ->nullOnDelete();

            $table->foreignId('atasan_id')
                  ->nullable()
                  ->after('bagian_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        // 3. Baru pasang foreign key kepala_bagian_id di tabel bagian,
        //    setelah tabel users sudah lengkap kolomnya
        Schema::table('bagian', function (Blueprint $table) {
            $table->foreign('kepala_bagian_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bagian', function (Blueprint $table) {
            $table->dropForeign(['kepala_bagian_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bagian_id']);
            $table->dropForeign(['atasan_id']);
            $table->dropColumn(['nip', 'role', 'bagian_id', 'atasan_id']);
        });

        Schema::dropIfExists('bagian');
    }
};