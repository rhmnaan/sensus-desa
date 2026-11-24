<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dusun', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_dusun', 100)->unique();
        });

        Schema::create('rw', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dusun_id')->constrained('dusun')->restrictOnDelete();
            $table->string('no_rw', 10);
            $table->unique(['dusun_id', 'no_rw']);
        });

        Schema::create('rt', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rw_id')->constrained('rw')->restrictOnDelete();
            $table->string('no_rt', 10);
            $table->unique(['rw_id', 'no_rt']);
        });

        Schema::create('pekerjaan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_pekerjaan', 100);
        });

        Schema::create('jenis_usaha', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_jenis', 100);
        });

        Schema::create('jenis_aset', function (Blueprint $table): void {
            $table->id();
            $table->string('kategori', 50);
            $table->string('nama_aset', 100);
            $table->string('satuan', 30);
        });

        Schema::create('jenis_bantuan', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama_bantuan', 100);
        });

        Schema::create('kategori_kesejahteraan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_kategori', 100);
            $table->decimal('skor_min', 8, 2);
            $table->decimal('skor_max', 8, 2);
            $table->boolean('layak_bantuan')->default(false);
        });

        Schema::create('periode_sensus', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 100);
            $table->unsignedSmallInteger('tahun');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->string('status', 20)->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_sensus');
        Schema::dropIfExists('kategori_kesejahteraan');
        Schema::dropIfExists('jenis_bantuan');
        Schema::dropIfExists('jenis_aset');
        Schema::dropIfExists('jenis_usaha');
        Schema::dropIfExists('pekerjaan');
        Schema::dropIfExists('rt');
        Schema::dropIfExists('rw');
        Schema::dropIfExists('dusun');
    }
};
