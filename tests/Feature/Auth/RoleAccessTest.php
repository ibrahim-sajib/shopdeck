<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_owner_dashboard(): void
    {
        $response = $this->get('/owner/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_shop_owner_cannot_access_admin_dashboard(): void
    {
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($owner)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_owner_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/owner/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }

    public function test_shop_owner_can_access_owner_dashboard(): void
    {
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($owner)->get('/owner/dashboard');

        $response->assertOk();
    }

    public function test_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_redirects_shop_owner_to_owner_dashboard(): void
    {
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_user_is_blocked_by_middleware(): void
    {
        $inactiveAdmin = User::factory()->admin()->inactive()->create();

        $response = $this->actingAs($inactiveAdmin)->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
