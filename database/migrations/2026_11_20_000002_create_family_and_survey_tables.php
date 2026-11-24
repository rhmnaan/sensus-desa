<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keluarga', function (Blueprint $table): void {
            $table->id();
            $table->string('no_kk', 16)->unique();
            $table->text('alamat')->nullable();
            $table->foreignId('rt_id')->constrained('rt')->restrictOnDelete();
            $table->timestamps();
            $table->index('rt_id');
        });

        Schema::create('anggota_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluarga')->restrictOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('nama', 100);
            $table->char('jenis_kelamin', 1);
            $table->date('tanggal_lahir');
            $table->string('hubungan_keluarga', 20);
            $table->string('status_perkawinan', 20);
            $table->string('pendidikan_terakhir', 30);
            $table->string('status_sekolah', 30);
            $table->foreignId('pekerjaan_utama_id')->nullable()->constrained('pekerjaan')->restrictOnDelete();
            $table->foreignId('pekerjaan_sampingan_id')->nullable()->constrained('pekerjaan')->restrictOnDelete();
            $table->boolean('disabilitas')->default(false);
            $table->boolean('penyakit_kronis')->default(false);
            $table->index('keluarga_id');
            $table->index('pekerjaan_utama_id');
            $table->index('pekerjaan_sampingan_id');
        });

        Schema::create('pendataan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluarga')->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('periode_sensus')->restrictOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->date('tgl_pendataan');
            $table->unique(['keluarga_id', 'periode_id']);
            $table->index('periode_id');
            $table->index('petugas_id');
        });

        Schema::create('data_ekonomi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pendataan_id')->unique()->constrained('pendataan')->restrictOnDelete();
            $table->decimal('penghasilan_bulanan', 15, 2);
            $table->decimal('pengeluaran_makan', 15, 2);
            $table->decimal('pengeluaran_pendidikan', 15, 2);
            $table->decimal('pengeluaran_listrik_air', 15, 2);
            $table->decimal('pengeluaran_kesehatan', 15, 2);
            $table->decimal('pengeluaran_lainnya', 15, 2);
            $table->decimal('jumlah_tabungan', 15, 2);
            $table->decimal('jumlah_utang', 15, 2);
            $table->foreignId('jenis_usaha_id')->nullable()->constrained('jenis_usaha')->restrictOnDelete();
            $table->decimal('omzet_bulanan', 15, 2);
            $table->unsignedInteger('jumlah_tenaga_kerja');
            $table->index('jenis_usaha_id');
        });

        Schema::create('kondisi_rumah', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pendataan_id')->unique()->constrained('pendataan')->restrictOnDelete();
            $table->string('status_kepemilikan', 30);
            $table->string('jenis_lantai', 30);
            $table->string('jenis_dinding', 30);
            $table->string('jenis_atap', 30);
            $table->decimal('luas_lantai', 10, 2)->nullable();
            $table->string('sumber_air', 30);
            $table->string('sumber_penerangan', 30);
            $table->string('bahan_bakar_masak', 30);
            $table->string('fasilitas_mck', 30);
        });

        Schema::create('aset_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pendataan_id')->constrained('pendataan')->restrictOnDelete();
            $table->foreignId('jenis_aset_id')->constrained('jenis_aset')->restrictOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('keterangan', 255)->nullable();
            $table->unique(['pendataan_id', 'jenis_aset_id']);
            $table->index('jenis_aset_id');
        });

        Schema::create('bantuan_sosial', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pendataan_id')->constrained('pendataan')->restrictOnDelete();
            $table->foreignId('jenis_bantuan_id')->constrained('jenis_bantuan')->restrictOnDelete();
            $table->unsignedSmallInteger('tahun_terima');
            $table->string('keterangan', 255)->nullable();
            $table->unique(['pendataan_id', 'jenis_bantuan_id', 'tahun_terima']);
            $table->index('jenis_bantuan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bantuan_sosial');
        Schema::dropIfExists('aset_keluarga');
        Schema::dropIfExists('kondisi_rumah');
        Schema::dropIfExists('data_ekonomi');
        Schema::dropIfExists('pendataan');
        Schema::dropIfExists('anggota_keluarga');
        Schema::dropIfExists('keluarga');
    }
};
