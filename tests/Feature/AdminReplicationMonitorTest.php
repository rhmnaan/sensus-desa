<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RiwayatSinkronisasi;
use Mockery;
use Tests\TestCase;

class AdminReplicationMonitorTest extends TestCase
{
    public function test_admin_monitor_shows_agent_history_and_recent_errors(): void
    {
        $monitor = [
            'koneksi' => 'terhubung',
            'pesanKoneksi' => null,
            'database' => 'sensus_desa',
            'agenTersedia' => true,
            'agen' => [
                [
                    'jenis' => 'Merge',
                    'publikasi' => 'Publikasi Desa',
                    'job' => 'Merge Agent Job',
                    'status' => 'Berjalan',
                    'mulai' => '23 Nov 2026 14:00',
                ],
            ],
            'riwayatTersedia' => true,
            'riwayat' => [
                [
                    'waktu' => '23 Nov 2026 14:00',
                    'publikasi' => 'Publikasi Desa',
                    'jenis' => 'Merge',
                    'status' => 'Gagal',
                    'pesan' => 'Koneksi subscriber gagal.',
                ],
            ],
            'kesalahan' => [
                [
                    'waktu' => '23 Nov 2026 14:00',
                    'publikasi' => 'Publikasi Desa',
                    'jenis' => 'Merge',
                    'status' => 'Gagal',
                    'pesan' => 'Koneksi subscriber gagal.',
                ],
            ],
            'konflik' => 2,
        ];
        $service = Mockery::mock(RiwayatSinkronisasi::class);
        $service->shouldReceive('monitor')->once()->andReturn($monitor);
        $this->app->instance(RiwayatSinkronisasi::class, $service);

        $response = $this->actingAs(new User(['role' => 'admin']))
            ->get('/admin/replikasi');

        $response->assertOk()
            ->assertSee('Monitor Replikasi')
            ->assertSee('Perbarui data')
            ->assertSee('href="'.url('/admin/replikasi').'"', false)
            ->assertDontSee('Konflik Sinkronisasi')
            ->assertSee('SQL Server terhubung')
            ->assertSee('Merge Agent Job')
            ->assertSee('Publikasi Desa')
            ->assertSee('Berjalan')
            ->assertSee('Sinkronisasi berhasil terakhir')
            ->assertSee('Error terbaru')
            ->assertSee('Koneksi subscriber gagal.')
            ->assertSee('Konflik tercatat');
    }

    public function test_petugas_cannot_access_admin_replication_monitor(): void
    {
        $response = $this->actingAs(new User(['role' => 'petugas']))
            ->get('/admin/replikasi');

        $response->assertForbidden();
    }

    public function test_monitor_explains_when_the_database_is_not_sql_server(): void
    {
        $response = $this->actingAs(new User(['role' => 'admin']))
            ->get('/admin/replikasi');

        $response->assertOk()
            ->assertSee('Monitor memerlukan koneksi SQL Server.')
            ->assertSee('Informasi agen tidak tersedia.')
            ->assertSee('Riwayat error belum tersedia.')
            ->assertSee('Konflik tercatat');
    }
}
