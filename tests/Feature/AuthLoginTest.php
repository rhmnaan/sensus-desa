<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthLoginTest extends TestCase
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
    }

    public function test_active_admin_can_log_in_and_is_redirected_to_admin_dashboard(): void
    {
        User::query()->create([
            'nama' => 'Admin',
            'username' => 'admin',
            'password' => 'password-admin',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'username' => 'admin',
            'password' => 'password-admin',
        ])->assertRedirect('/admin/dashboard');

        $this->assertAuthenticated();
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        User::query()->create([
            'nama' => 'Admin',
            'username' => 'admin',
            'password' => 'password-admin',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $this->from('/login')
            ->post('/login', [
                'username' => 'admin',
                'password' => 'password-admin',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}
