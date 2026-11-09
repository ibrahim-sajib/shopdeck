<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShopOwnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_shop_owners_list(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->shopOwner()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/shop-owners');

        $response->assertOk();
    }

    public function test_shop_owner_cannot_view_shop_owners_list(): void
    {
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($owner)->get('/admin/shop-owners');

        $response->assertForbidden();
    }

    public function test_admin_can_create_shop_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->create(['owner_id' => null]);

        $response = $this->actingAs($admin)->post('/admin/shop-owners', [
            'name' => 'Alice Merchant',
            'email' => 'alice@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/shop-owners');
        $this->assertDatabaseHas('users', [
            'name' => 'Alice Merchant',
            'email' => 'alice@example.com',
            'role' => User::ROLE_SHOP_OWNER,
            'is_active' => true,
        ]);

        $owner = User::where('email', 'alice@example.com')->first();
        $this->assertTrue(Hash::check('secret1234', $owner->password));
        $this->assertEquals($owner->id, $shop->fresh()->owner_id);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($admin)->post('/admin/shop-owners', [
            'name' => 'Another Merchant',
            'email' => 'duplicate@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_shop_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->shopOwner()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->put("/admin/shop-owners/{$owner->id}", [
            'name' => 'Updated Name',
            'email' => $owner->email,
            'is_active' => false,
        ]);

        $response->assertRedirect('/admin/shop-owners');
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_shop_owner_status(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->shopOwner()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/admin/shop-owners/{$owner->id}/toggle-status");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_shop_owner_and_unassign_shop(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($admin)->delete("/admin/shop-owners/{$owner->id}");

        $response->assertRedirect('/admin/shop-owners');
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertNull($shop->fresh()->owner_id);
    }
}
