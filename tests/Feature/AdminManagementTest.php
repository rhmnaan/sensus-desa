<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminManagementTest extends TestCase
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
            $table->string('nama_dusun')->unique();
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
            $table->unsignedBigInteger('rt_id');
            $table->string('no_kk');
        });
        Schema::create('pendataan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('keluarga_id');
            $table->unsignedBigInteger('periode_id');
        });
        Schema::create('periode_sensus', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('tahun');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->string('status');
        });
        Schema::create('pekerjaan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_pekerjaan');
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
    }

    public function test_admin_can_create_update_deactivate_and_reset_a_user(): void
    {
        $admin = User::query()->create([
            'nama' => 'Admin',
            'username' => 'admin',
            'password' => 'password-admin',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $this->post('/admin/pengguna', [
            'nama' => 'Petugas Baru',
            'username' => 'petugas_baru',
            'role' => 'petugas',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertRedirect();

        $user = User::query()->where('username', 'petugas_baru')->firstOrFail();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => true]);

        $this->put("/admin/pengguna/{$user->id}", [
            'nama' => 'Petugas Diperbarui',
            'username' => 'petugas_baru',
            'role' => 'petugas',
        ])->assertRedirect();

        $this->put("/admin/pengguna/{$user->id}/password", [
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect();
        $this->assertTrue(password_verify('password-baru', $user->fresh()->password));

        $this->patch("/admin/pengguna/{$user->id}/aktif")->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }

    public function test_admin_cannot_demote_the_only_active_admin(): void
    {
        $admin = User::query()->create([
            'nama' => 'Admin',
            'username' => 'admin',
            'password' => 'password-admin',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from('/admin/pengguna')
            ->put("/admin/pengguna/{$admin->id}", [
                'nama' => 'Admin',
                'username' => 'admin',
                'role' => 'petugas',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('pengguna');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_can_manage_hierarchical_regions_and_cannot_delete_used_regions(): void
    {
        $this->actingAs(new User(['role' => 'admin']));
        $this->post('/admin/wilayah/dusun', ['nama_dusun' => 'Dusun A'])->assertRedirect();
        $dusunId = DB::table('dusun')->value('id');
        $this->post('/admin/wilayah/rw', ['dusun_id' => $dusunId, 'no_rw' => '01'])->assertRedirect();
        $rwId = DB::table('rw')->value('id');
        $this->post('/admin/wilayah/rt', ['rw_id' => $rwId, 'no_rt' => '02'])->assertRedirect();
        $rtId = DB::table('rt')->value('id');
        DB::table('keluarga')->insert(['rt_id' => $rtId, 'no_kk' => '1234567890123456']);

        $this->get('/admin/wilayah')->assertOk()->assertSee('Dusun A');
        $this->delete("/admin/wilayah/rt/{$rtId}")
            ->assertSessionHasErrors('wilayah');
        $this->assertDatabaseHas('rt', ['id' => $rtId]);
    }

    public function test_only_one_census_period_can_be_active_and_an_active_period_can_be_ended(): void
    {
        $this->actingAs(new User(['role' => 'admin']));
        $payload = [
            'nama' => 'Sensus 2026',
            'tahun' => 2026,
            'tgl_mulai' => '2026-01-01',
            'tgl_selesai' => '2026-12-31',
            'status' => 'berjalan',
        ];
        $this->post('/admin/periode', $payload)->assertRedirect();
        $periodeId = DB::table('periode_sensus')->value('id');

        $payload['nama'] = 'Sensus 2027';
        $payload['tahun'] = 2027;
        $payload['tgl_mulai'] = '2027-01-01';
        $payload['tgl_selesai'] = '2027-12-31';
        $this->from('/admin/periode')
            ->post('/admin/periode', $payload)
            ->assertRedirect('/admin/periode')
            ->assertSessionHasErrors('status');

        $payload['status'] = 'selesai';
        $this->put("/admin/periode/{$periodeId}", $payload)->assertRedirect();
        $this->assertDatabaseHas('periode_sensus', ['id' => $periodeId, 'status' => 'selesai']);
    }

    public function test_admin_can_manage_each_master_data_type(): void
    {
        $this->actingAs(new User(['role' => 'admin']));

        foreach ([
            ['pekerjaan', ['nama_pekerjaan' => 'Petani'], 'nama_pekerjaan', 'Petani'],
            ['usaha', ['nama_jenis' => 'Pertanian'], 'nama_jenis', 'Pertanian'],
            ['aset', ['kategori' => 'Ternak', 'nama_aset' => 'Sapi', 'satuan' => 'ekor'], 'nama_aset', 'Sapi'],
            ['bantuan', ['kode' => 'PKH', 'nama_bantuan' => 'PKH'], 'nama_bantuan', 'PKH'],
            ['kategori', [
                'nama_kategori' => 'Miskin',
                'skor_min' => 0,
                'skor_max' => 50,
                'layak_bantuan' => 1,
            ], 'nama_kategori', 'Miskin'],
        ] as [$jenis, $payload, $kolom, $nilai]) {
            $this->post("/admin/master/{$jenis}", $payload)->assertRedirect();
            $this->assertDatabaseHas(match ($jenis) {
                'pekerjaan' => 'pekerjaan',
                'usaha' => 'jenis_usaha',
                'aset' => 'jenis_aset',
                'bantuan' => 'jenis_bantuan',
                'kategori' => 'kategori_kesejahteraan',
            }, [$kolom => $nilai]);
        }

        $this->get('/admin/master')->assertOk()
            ->assertSee('Pekerjaan')
            ->assertSee('Jenis Usaha')
            ->assertSee('Kategori Kesejahteraan');
    }

    public function test_admin_pages_are_available_to_admin_and_not_to_other_roles(): void
    {
        $admin = new User(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get('/admin/dashboard')->assertOk()->assertSee('Progres pendataan per dusun');
        $this->get('/admin/pengguna')->assertOk()->assertSee('Tambah pengguna');
        $this->get('/admin/wilayah')->assertOk()->assertSee('Dusun');
        $this->get('/admin/periode')->assertOk()->assertSee('Tambah periode');
        $this->get('/admin/penilaian')->assertOk()->assertSee('Rumus, bobot indikator');

        $this->actingAs(new User(['role' => 'petugas']))
            ->withoutMiddleware([Authenticate::class])
            ->get('/admin/pengguna')
            ->assertForbidden();
    }
}
