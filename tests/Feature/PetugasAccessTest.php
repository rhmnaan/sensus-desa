<?php

namespace Tests\Feature;

use App\Http\Middleware\PeranMiddleware;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PetugasAccessTest extends TestCase
{
    public function test_petugas_role_is_normalized_and_uses_petugas_navigation(): void
    {
        Route::middleware(['auth', PeranMiddleware::class.':petugas'])
            ->get('/testing/petugas-navigation', fn () => view('placeholder', ['judul' => 'Tes Petugas']));

        $response = $this->actingAs(new User(['role' => ' PETUGAS LAPANGAN ']))
            ->get('/testing/petugas-navigation');

        $response->assertOk()
            ->assertSee('Petugas Lapangan')
            ->assertSee('Daftar Keluarga')
            ->assertSee('Form Pendataan')
            ->assertSee('href="'.url('/petugas/pendataan').'"', false)
            ->assertDontSee('Status Sinkron')
            ->assertDontSee('Administrator')
            ->assertDontSee('Monitor Replikasi');
    }

    public function test_non_petugas_role_cannot_access_petugas_routes(): void
    {
        Route::middleware(['auth', PeranMiddleware::class.':petugas'])
            ->get('/testing/petugas-only', fn () => response()->noContent());

        $this->actingAs(new User(['role' => 'Admin']))
            ->get('/testing/petugas-only')
            ->assertForbidden();
    }

    public function test_logged_in_petugas_is_redirected_to_petugas_dashboard(): void
    {
        $this->actingAs(new User(['role' => ' PETUGAS ']))
            ->get('/login')
            ->assertRedirect('/petugas/dashboard');
    }
}
