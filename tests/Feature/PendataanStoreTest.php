<?php

namespace Tests\Feature;

use App\Http\Middleware\PeranMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PendataanStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('keluarga', function (Blueprint $table): void {
            $table->id();
            $table->string('no_kk', 16);
            $table->unsignedBigInteger('rt_id');
            $table->timestamps();
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
            $table->unique(['keluarga_id', 'periode_id']);
        });

        Schema::create('jenis_usaha', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_jenis');
        });

        Schema::create('jenis_aset', function (Blueprint $table): void {
            $table->id();
            $table->string('kategori');
            $table->string('nama_aset');
            $table->string('satuan');
        });

        Schema::create('jenis_bantuan', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
            $table->string('nama_bantuan');
        });

        Schema::create('data_ekonomi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id')->unique();
            $table->decimal('penghasilan_bulanan', 15, 2);
            $table->decimal('pengeluaran_makan', 15, 2);
            $table->decimal('pengeluaran_pendidikan', 15, 2);
            $table->decimal('pengeluaran_listrik_air', 15, 2);
            $table->decimal('pengeluaran_kesehatan', 15, 2);
            $table->decimal('pengeluaran_lainnya', 15, 2);
            $table->decimal('jumlah_tabungan', 15, 2);
            $table->decimal('jumlah_utang', 15, 2);
            $table->unsignedBigInteger('jenis_usaha_id')->nullable();
            $table->decimal('omzet_bulanan', 15, 2);
            $table->unsignedInteger('jumlah_tenaga_kerja');
        });

        Schema::create('kondisi_rumah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id')->unique();
            $table->string('status_kepemilikan');
            $table->string('jenis_lantai');
            $table->string('jenis_dinding');
            $table->string('jenis_atap');
            $table->decimal('luas_lantai', 7, 2)->nullable();
            $table->string('sumber_air');
            $table->string('sumber_penerangan');
            $table->string('bahan_bakar_masak');
            $table->string('fasilitas_mck');
        });

        Schema::create('aset_keluarga', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->unsignedBigInteger('jenis_aset_id');
            $table->decimal('jumlah', 10, 2);
            $table->string('keterangan')->nullable();
            $table->unique(['pendataan_id', 'jenis_aset_id']);
        });

        Schema::create('bantuan_sosial', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pendataan_id');
            $table->unsignedBigInteger('jenis_bantuan_id');
            $table->unsignedInteger('tahun_terima');
            $table->string('keterangan')->nullable();
            $table->unique(['pendataan_id', 'jenis_bantuan_id', 'tahun_terima']);
        });
    }

    public function test_survey_data_is_saved_and_updated_for_the_active_period(): void
    {
        $keluargaId = DB::table('keluarga')->insertGetId([
            'no_kk' => '1234567890123456',
            'rt_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('periode_sensus')->insert([
            'nama' => 'Sensus 2026',
            'tahun' => 2026,
            'status' => 'berjalan',
        ]);
        DB::table('jenis_usaha')->insert(['id' => 1, 'nama_jenis' => 'Pertanian']);
        DB::table('jenis_aset')->insert([
            'id' => 1,
            'kategori' => 'ternak',
            'nama_aset' => 'Sapi',
            'satuan' => 'ekor',
        ]);
        DB::table('jenis_bantuan')->insert([
            'id' => 1,
            'kode' => 'PKH',
            'nama_bantuan' => 'Program Keluarga Harapan',
        ]);

        $payload = [
            'keluarga_id' => $keluargaId,
            'penghasilan_bulanan' => 1000000,
            'pengeluaran_makan' => 300000,
            'pengeluaran_pendidikan' => 100000,
            'pengeluaran_listrik_air' => 50000,
            'pengeluaran_kesehatan' => 25000,
            'pengeluaran_lainnya' => 50000,
            'jumlah_tabungan' => 200000,
            'jumlah_utang' => 0,
            'jenis_usaha_id' => 1,
            'omzet_bulanan' => 1500000,
            'jumlah_tenaga_kerja' => 2,
            'status_kepemilikan' => 'milik_sendiri',
            'jenis_lantai' => 'semen',
            'jenis_dinding' => 'tembok',
            'jenis_atap' => 'genteng',
            'luas_lantai' => 50,
            'sumber_air' => 'sumur',
            'sumber_penerangan' => 'listrik_pln',
            'bahan_bakar_masak' => 'lpg',
            'fasilitas_mck' => 'sendiri',
            'aset' => [
                ['jenis_aset_id' => 1, 'jumlah' => 2, 'keterangan' => ''],
            ],
            'bantuan' => [
                ['jenis_bantuan_id' => 1, 'tahun_terima' => 2026, 'keterangan' => ''],
            ],
        ];

        $response = $this->withoutMiddleware([Authenticate::class, PeranMiddleware::class])
            ->post('/petugas/pendataan', $payload);

        $response->assertRedirect('/petugas/keluarga')
            ->assertSessionHas('status', 'Data pendataan keluarga berhasil disimpan.');
        $this->assertDatabaseHas('pendataan', [
            'keluarga_id' => $keluargaId,
            'periode_id' => 1,
        ]);
        $pendataanId = DB::table('pendataan')
            ->where('keluarga_id', $keluargaId)
            ->where('periode_id', 1)
            ->value('id');
        $this->assertDatabaseHas('data_ekonomi', [
            'pendataan_id' => $pendataanId,
            'penghasilan_bulanan' => 1000000,
            'jenis_usaha_id' => 1,
        ]);
        $this->assertDatabaseHas('kondisi_rumah', [
            'pendataan_id' => $pendataanId,
            'status_kepemilikan' => 'milik_sendiri',
            'luas_lantai' => 50,
        ]);
        $this->assertDatabaseHas('aset_keluarga', [
            'pendataan_id' => $pendataanId,
            'jenis_aset_id' => 1,
            'jumlah' => 2,
        ]);
        $this->assertDatabaseHas('bantuan_sosial', [
            'pendataan_id' => $pendataanId,
            'jenis_bantuan_id' => 1,
            'tahun_terima' => 2026,
        ]);

        $payload['penghasilan_bulanan'] = 1750000;
        unset($payload['aset'], $payload['bantuan']);

        $this->post('/petugas/pendataan', $payload)->assertRedirect('/petugas/keluarga');

        $this->assertDatabaseCount('pendataan', 1);
        $this->assertDatabaseHas('data_ekonomi', ['penghasilan_bulanan' => 1750000]);
        $this->assertDatabaseCount('aset_keluarga', 0);
        $this->assertDatabaseCount('bantuan_sosial', 0);
    }
}
