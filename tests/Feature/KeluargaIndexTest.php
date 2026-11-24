<?php

namespace Tests\Feature;

use App\Http\Middleware\PeranMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KeluargaIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        Schema::create('pekerjaan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_pekerjaan');
        });

        Schema::create('keluarga', function (Blueprint $table): void {
            $table->id();
            $table->string('no_kk', 16)->unique();
            $table->text('alamat')->nullable();
            $table->unsignedBigInteger('rt_id');
            $table->timestamps();
        });

        Schema::create('anggota_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('keluarga_id');
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('jenis_kelamin');
            $table->date('tanggal_lahir');
            $table->string('hubungan_keluarga');
            $table->string('status_perkawinan');
            $table->string('pendidikan_terakhir');
            $table->string('status_sekolah');
            $table->unsignedBigInteger('pekerjaan_utama_id')->nullable();
            $table->unsignedBigInteger('pekerjaan_sampingan_id')->nullable();
            $table->boolean('disabilitas')->default(false);
            $table->boolean('penyakit_kronis')->default(false);
        });

        Schema::create('periode_sensus', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('tahun');
            $table->string('status');
        });

        Schema::create('pendataan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('keluarga_id');
            $table->unsignedBigInteger('periode_id');
            $table->unsignedBigInteger('petugas_id')->nullable();
            $table->date('tgl_pendataan');
        });
    }

    public function test_family_index_shows_live_records_and_current_period_status(): void
    {
        $wilayah = $this->buatWilayah();
        $periodeId = DB::table('periode_sensus')->insertGetId([
            'nama' => 'Sensus 2026',
            'tahun' => 2026,
            'status' => 'berjalan',
        ]);
        $keluargaTerdata = $this->buatKeluarga($wilayah['rt'], '1234567890123456', 'Kepala Terdata', '1111111111111111');
        $this->buatKeluarga($wilayah['rt'], '1234567890123457', 'Kepala Belum', '1111111111111112');
        DB::table('pendataan')->insert([
            'keluarga_id' => $keluargaTerdata,
            'periode_id' => $periodeId,
            'tgl_pendataan' => '2026-01-01',
        ]);

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])->get('/petugas/keluarga');

        $response->assertOk()
            ->assertSee('Kepala Terdata')
            ->assertSee('Kepala Belum')
            ->assertSee('Terdata')
            ->assertSee('Belum')
            ->assertDontSee('Status sinkron')
            ->assertDontSee('Merge terkonfigurasi')
            ->assertDontSee('Merge belum dikonfigurasi')
            ->assertSee('Lanjutkan')
            ->assertSee('RT 01 / RW 01')
            ->assertSee('2 keluarga terdaftar')
            ->assertDontSee('Budi Santoso');
    }

    public function test_petugas_dashboard_shows_pending_families_from_the_database(): void
    {
        $wilayah = $this->buatWilayah();
        $this->buatKeluarga($wilayah['rt'], '1234567890123456', 'Kepala Dashboard', '1111111111111111');

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->get('/petugas/dashboard');

        $response->assertOk()
            ->assertSee('Dashboard Petugas')
            ->assertSee('Kepala Dashboard')
            ->assertSee('Tambah keluarga')
            ->assertSee('Daftar keluarga')
            ->assertDontSee('Lihat sinkron');
    }

    public function test_pendataan_sidebar_destination_shows_family_picker(): void
    {
        $wilayah = $this->buatWilayah();
        $keluargaId = $this->buatKeluarga($wilayah['rt'], '1234567890123456', 'Kepala Pilihan', '1111111111111111');
        DB::table('periode_sensus')->insert([
            'nama' => 'Sensus 2026',
            'tahun' => 2026,
            'status' => 'berjalan',
        ]);

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->get('/petugas/pendataan');

        $response->assertOk()
            ->assertSee('Pilih keluarga')
            ->assertSee('Kepala Pilihan')
            ->assertSee('Mulai pendataan')
        ->assertSee(url('/petugas/pendataan?keluarga_id='.$keluargaId), false);
    }

    public function test_family_index_filters_by_nik_rt_and_current_period_status(): void
    {
        $wilayah = $this->buatWilayah();
        $wilayahLain = $this->buatWilayah('02', '02');
        $periodeId = DB::table('periode_sensus')->insertGetId([
            'nama' => 'Sensus 2026',
            'tahun' => 2026,
            'status' => 'berjalan',
        ]);
        $keluargaTerdata = $this->buatKeluarga($wilayah['rt'], '1234567890123456', 'Kepala Cocok', '2222222222222222');
        $this->buatKeluarga($wilayahLain['rt'], '1234567890123457', 'Kepala Lain', '2222222222222223');
        DB::table('pendataan')->insert([
            'keluarga_id' => $keluargaTerdata,
            'periode_id' => $periodeId,
            'tgl_pendataan' => '2026-01-01',
        ]);

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])->get('/petugas/keluarga?'.http_build_query([
            'cari' => '2222222222222222',
            'rt_id' => $wilayah['rt'],
            'status' => 'terdata',
        ]));

        $response->assertOk()
            ->assertSee('Kepala Cocok')
            ->assertDontSee('Kepala Lain');
    }

    public function test_family_form_uses_regions_and_jobs_from_the_database(): void
    {
        $this->buatWilayah();
        DB::table('pekerjaan')->insert(['nama_pekerjaan' => 'Petani']);

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->get('/petugas/keluarga/tambah');

        $response->assertOk()
            ->assertSee('Dusun Uji, RW 01, RT 01')
            ->assertSee('Petani');
    }

    public function test_family_form_saves_family_and_members_to_the_database(): void
    {
        $wilayah = $this->buatWilayah();
        $pekerjaanId = DB::table('pekerjaan')->insertGetId(['nama_pekerjaan' => 'Petani']);

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])->post('/petugas/keluarga/tambah', [
            'no_kk' => '3333333333333333',
            'rt_id' => $wilayah['rt'],
            'alamat' => 'Jalan Uji',
            'anggota' => [
                [
                    'nik' => '4444444444444444',
                    'nama' => 'Kepala Baru',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => '1990-01-01',
                    'hubungan_keluarga' => 'kepala',
                    'status_perkawinan' => 'kawin',
                    'pendidikan_terakhir' => 'sma',
                    'status_sekolah' => 'tidak_sekolah',
                    'pekerjaan_utama_id' => $pekerjaanId,
                    'disabilitas' => '1',
                ],
            ],
        ]);

        $response->assertRedirect('/petugas/keluarga')
            ->assertSessionHas('status');
        $this->assertDatabaseHas('keluarga', [
            'no_kk' => '3333333333333333',
            'alamat' => 'Jalan Uji',
            'rt_id' => $wilayah['rt'],
        ]);
        $this->assertDatabaseHas('anggota_keluarga', [
            'nik' => '4444444444444444',
            'nama' => 'Kepala Baru',
            'pekerjaan_utama_id' => $pekerjaanId,
            'disabilitas' => true,
        ]);
    }

    public function test_family_edit_form_loads_existing_data(): void
    {
        $wilayah = $this->buatWilayah();
        $keluargaId = $this->buatKeluarga($wilayah['rt'], '3333333333333333', 'Kepala Lama', '4444444444444444');
        $anggotaId = DB::table('anggota_keluarga')->where('keluarga_id', $keluargaId)->value('id');

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->get("/petugas/keluarga/{$keluargaId}/ubah");

        $response->assertOk()
            ->assertSee('Ubah Keluarga')
            ->assertSee('3333333333333333')
            ->assertSee('Kepala Lama')
            ->assertSee('"id":'.$anggotaId, false);
    }

    public function test_family_update_keeps_existing_unique_numbers_and_updates_members(): void
    {
        $wilayah = $this->buatWilayah();
        $keluargaId = $this->buatKeluarga($wilayah['rt'], '3333333333333333', 'Kepala Lama', '4444444444444444');
        $anggotaId = DB::table('anggota_keluarga')->where('keluarga_id', $keluargaId)->value('id');

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->put("/petugas/keluarga/{$keluargaId}", [
                'no_kk' => '3333333333333333',
                'rt_id' => $wilayah['rt'],
                'alamat' => 'Alamat baru',
                'anggota' => [
                    [
                        'id' => $anggotaId,
                        'nik' => '4444444444444444',
                        'nama' => 'Kepala Diperbarui',
                        'jenis_kelamin' => 'L',
                        'tanggal_lahir' => '1990-01-01',
                        'hubungan_keluarga' => 'kepala',
                        'status_perkawinan' => 'kawin',
                        'pendidikan_terakhir' => 'sma',
                        'status_sekolah' => 'tidak_sekolah',
                    ],
                    [
                        'nik' => '5555555555555555',
                        'nama' => 'Anggota Baru',
                        'jenis_kelamin' => 'P',
                        'tanggal_lahir' => '1995-01-01',
                        'hubungan_keluarga' => 'istri',
                        'status_perkawinan' => 'kawin',
                        'pendidikan_terakhir' => 'sma',
                        'status_sekolah' => 'tidak_sekolah',
                    ],
                ],
            ]);

        $response->assertRedirect("/petugas/keluarga/{$keluargaId}")
            ->assertSessionHas('status');
        $this->assertDatabaseHas('keluarga', [
            'id' => $keluargaId,
            'alamat' => 'Alamat baru',
        ]);
        $this->assertDatabaseHas('anggota_keluarga', [
            'id' => $anggotaId,
            'nama' => 'Kepala Diperbarui',
        ]);
        $this->assertDatabaseHas('anggota_keluarga', [
            'keluarga_id' => $keluargaId,
            'nik' => '5555555555555555',
        ]);
        $this->assertDatabaseCount('anggota_keluarga', 2);
    }

    public function test_family_detail_shows_members_and_survey_history(): void
    {
        $wilayah = $this->buatWilayah();
        $keluargaId = $this->buatKeluarga($wilayah['rt'], '3333333333333333', 'Kepala Detail', '4444444444444444');
        $periodeId = DB::table('periode_sensus')->insertGetId([
            'nama' => 'Sensus 2026',
            'tahun' => 2026,
            'status' => 'selesai',
        ]);
        DB::table('pendataan')->insert([
            'keluarga_id' => $keluargaId,
            'periode_id' => $periodeId,
            'tgl_pendataan' => '2026-01-02',
        ]);

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->get("/petugas/keluarga/{$keluargaId}");

        $response->assertOk()
            ->assertSee('Kepala Detail')
            ->assertSee('Sensus 2026')
            ->assertSee('02-01-2026');
    }

    public function test_family_form_requires_exactly_one_head_of_household(): void
    {
        $wilayah = $this->buatWilayah();

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])->post('/petugas/keluarga/tambah', [
            'no_kk' => '3333333333333333',
            'rt_id' => $wilayah['rt'],
            'anggota' => [
                [
                    'nik' => '4444444444444444',
                    'nama' => 'Anggota Bukan Kepala',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => '1990-01-01',
                    'hubungan_keluarga' => 'anak',
                    'status_perkawinan' => 'belum_kawin',
                    'pendidikan_terakhir' => 'sma',
                    'status_sekolah' => 'tidak_sekolah',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('anggota');
        $this->assertDatabaseMissing('keluarga', ['no_kk' => '3333333333333333']);
    }

    public function test_petugas_sync_page_is_removed(): void
    {
        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])->get('/petugas/sinkron');

        $response->assertNotFound();
    }

    public function test_petugas_sync_action_is_removed(): void
    {
        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->post('/petugas/sinkron');

        $response->assertNotFound();
    }

    /**
     * @return array{rt: int}
     */
    private function buatWilayah(string $noRw = '01', string $noRt = '01'): array
    {
        $dusunId = DB::table('dusun')->insertGetId(['nama_dusun' => 'Dusun Uji']);
        $rwId = DB::table('rw')->insertGetId([
            'dusun_id' => $dusunId,
            'no_rw' => $noRw,
        ]);
        $rtId = DB::table('rt')->insertGetId([
            'rw_id' => $rwId,
            'no_rt' => $noRt,
        ]);

        return ['rt' => $rtId];
    }

    private function buatKeluarga(int $rtId, string $noKk, string $nama, string $nik): int
    {
        $keluargaId = DB::table('keluarga')->insertGetId([
            'no_kk' => $noKk,
            'rt_id' => $rtId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('anggota_keluarga')->insert([
            'keluarga_id' => $keluargaId,
            'nik' => $nik,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1990-01-01',
            'hubungan_keluarga' => 'kepala',
            'status_perkawinan' => 'kawin',
            'pendidikan_terakhir' => 'sma',
            'status_sekolah' => 'tidak_sekolah',
            'disabilitas' => false,
            'penyakit_kronis' => false,
        ]);

        return $keluargaId;
    }
}
