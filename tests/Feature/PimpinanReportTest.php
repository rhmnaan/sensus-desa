<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PimpinanReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('no_hp')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('dusun', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_dusun');
        });
        Schema::create('rw', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dusun_id');
            $table->string('no_rw');
        });
        Schema::create('rt', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('rw_id');
            $table->string('no_rt');
        });
        Schema::create('keluarga', function (Blueprint $table): void {
            $table->id();
            $table->string('no_kk');
            $table->string('alamat');
            $table->unsignedBigInteger('rt_id');
        });
        Schema::create('pekerjaan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_pekerjaan');
        });
        Schema::create('anggota_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('keluarga_id');
            $table->string('nama');
            $table->string('hubungan_keluarga');
            $table->unsignedBigInteger('pekerjaan_utama_id')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
        });
        Schema::create('periode_sensus', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('tahun');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->string('status');
        });
        Schema::create('pendataan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('keluarga_id');
            $table->unsignedBigInteger('periode_id');
            $table->date('tgl_pendataan')->nullable();
        });
        Schema::create('kategori_kesejahteraan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_kategori');
            $table->decimal('skor_min', 7, 2);
            $table->decimal('skor_max', 7, 2);
            $table->boolean('layak_bantuan');
        });
        Schema::create('penilaian_kesejahteraan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->decimal('skor', 7, 2);
            $table->text('catatan')->nullable();
            $table->dateTime('dihitung_pada')->nullable();
        });
        Schema::create('data_ekonomi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->decimal('penghasilan_bulanan', 14, 2)->nullable();
            $table->decimal('jumlah_tabungan', 14, 2)->nullable();
            $table->decimal('jumlah_utang', 14, 2)->nullable();
        });
        Schema::create('kondisi_rumah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->string('status_kepemilikan')->nullable();
            $table->string('jenis_lantai')->nullable();
            $table->string('jenis_dinding')->nullable();
            $table->string('jenis_atap')->nullable();
            $table->string('sumber_air')->nullable();
            $table->string('sumber_penerangan')->nullable();
            $table->string('bahan_bakar_masak')->nullable();
            $table->string('fasilitas_mck')->nullable();
        });
        Schema::create('jenis_aset', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_aset');
        });
        Schema::create('aset_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->unsignedBigInteger('jenis_aset_id');
            $table->unsignedInteger('jumlah');
        });
        Schema::create('jenis_bantuan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_bantuan');
        });
        Schema::create('bantuan_sosial', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->unsignedBigInteger('jenis_bantuan_id');
        });

        DB::table('dusun')->insert(['id' => 1, 'nama_dusun' => 'Dusun Contoh']);
        DB::table('rw')->insert(['id' => 1, 'dusun_id' => 1, 'no_rw' => '01']);
        DB::table('rt')->insert(['id' => 1, 'rw_id' => 1, 'no_rt' => '02']);
        DB::table('keluarga')->insert([
            ['id' => 1, 'no_kk' => '1111111111111111', 'alamat' => 'Jalan Satu', 'rt_id' => 1],
            ['id' => 2, 'no_kk' => '2222222222222222', 'alamat' => 'Jalan Dua', 'rt_id' => 1],
        ]);
        DB::table('pekerjaan')->insert(['id' => 1, 'nama_pekerjaan' => 'Petani']);
        DB::table('anggota_keluarga')->insert([
            ['keluarga_id' => 1, 'nama' => 'Kepala Satu', 'hubungan_keluarga' => 'kepala', 'pekerjaan_utama_id' => 1, 'pendidikan_terakhir' => 'SMA'],
            ['keluarga_id' => 2, 'nama' => 'Kepala Dua', 'hubungan_keluarga' => 'kepala', 'pekerjaan_utama_id' => 1, 'pendidikan_terakhir' => 'SMP'],
        ]);
        DB::table('periode_sensus')->insert([
            'id' => 1, 'nama' => 'Sensus 2026', 'tahun' => 2026,
            'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-12-31', 'status' => 'berjalan',
        ]);
        DB::table('pendataan')->insert([
            'id' => 1, 'keluarga_id' => 1, 'periode_id' => 1, 'tgl_pendataan' => '2026-05-01',
        ]);
        DB::table('kategori_kesejahteraan')->insert([
            'id' => 1, 'nama_kategori' => 'Perlu Bantuan', 'skor_min' => 0, 'skor_max' => 50, 'layak_bantuan' => true,
        ]);
        DB::table('penilaian_kesejahteraan')->insert([
            'pendataan_id' => 1, 'skor' => 20, 'catatan' => 'Tinjau kembali saat verifikasi lapangan.',
        ]);
        DB::table('data_ekonomi')->insert([
            'pendataan_id' => 1, 'penghasilan_bulanan' => 1500000, 'jumlah_tabungan' => 100000, 'jumlah_utang' => 50000,
        ]);
        DB::table('kondisi_rumah')->insert([
            'pendataan_id' => 1, 'status_kepemilikan' => 'Milik sendiri', 'jenis_lantai' => 'Semen',
            'jenis_dinding' => 'Tembok', 'jenis_atap' => 'Genteng', 'sumber_air' => 'Sumur',
            'sumber_penerangan' => 'Listrik', 'bahan_bakar_masak' => 'Gas', 'fasilitas_mck' => 'Sendiri',
        ]);
        DB::table('jenis_aset')->insert(['id' => 1, 'nama_aset' => 'Sepeda Motor']);
        DB::table('aset_keluarga')->insert(['pendataan_id' => 1, 'jenis_aset_id' => 1, 'jumlah' => 1]);
        DB::table('jenis_bantuan')->insert(['id' => 1, 'nama_bantuan' => 'Bantuan Contoh']);
        DB::table('bantuan_sosial')->insert(['pendataan_id' => 1, 'jenis_bantuan_id' => 1]);
    }

    public function test_pimpinan_can_view_dashboard_statistics_assistance_and_report(): void
    {
        $this->actingAs(new User(['role' => 'pimpinan']));

        $this->get('/pimpinan/dashboard')
            ->assertOk()
            ->assertSee('Total Keluarga')
            ->assertSee('Perlu Bantuan')
            ->assertSee('Dusun Contoh');

        $this->get('/pimpinan/statistik')
            ->assertOk()
            ->assertSee('Petani')
            ->assertSee('SMA')
            ->assertSee('Sepeda Motor')
            ->assertSee('Bantuan Contoh');

        $this->get('/pimpinan/layak-bantuan')
            ->assertOk()
            ->assertSee('Kepala Satu')
            ->assertSee('1111111111111111')
            ->assertDontSee('2222222222222222');

        $this->get('/pimpinan/layak-bantuan/1')
            ->assertOk()
            ->assertSee('Tinjau kembali saat verifikasi lapangan.')
            ->assertSee('tidak dapat diubah');

        $this->get('/pimpinan/laporan?tingkat=rt')
            ->assertOk()
            ->assertSee('Dusun Contoh')
            ->assertSee('Sudah didata');

        $response = $this->get('/pimpinan/laporan/ekspor?tingkat=rt');
        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Dusun Contoh', $response->streamedContent());
    }

    public function test_non_pimpinan_cannot_access_leadership_pages(): void
    {
        $this->actingAs(new User(['role' => 'admin']))
            ->get('/pimpinan/dashboard')
            ->assertForbidden();
    }
}
