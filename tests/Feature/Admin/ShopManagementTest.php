<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_shops_list(): void
    {
        $admin = User::factory()->admin()->create();
        Shop::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/shops');

        $response->assertOk();
    }

    public function test_shop_owner_cannot_view_shops_list(): void
    {
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($owner)->get('/admin/shops');

        $response->assertForbidden();
    }

    public function test_admin_can_create_shop(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($admin)->post('/admin/shops', [
            'name' => 'Brand New Shop',
            'slug' => 'brand-new-shop',
            'owner_id' => $owner->id,
            'description' => 'A wonderful test shop.',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/shops');
        $this->assertDatabaseHas('shops', [
            'name' => 'Brand New Shop',
            'slug' => 'brand-new-shop',
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);
    }

    public function test_shop_creation_auto_generates_slug_if_omitted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/shops', [
            'name' => 'Auto Slug Shop',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/shops');
        $this->assertDatabaseHas('shops', [
            'name' => 'Auto Slug Shop',
            'slug' => 'auto-slug-shop',
        ]);
    }

    public function test_duplicate_slug_is_rejected_with_validation_error(): void
    {
        $admin = User::factory()->admin()->create();
        Shop::factory()->create(['slug' => 'existing-slug']);

        $response = $this->actingAs($admin)->post('/admin/shops', [
            'name' => 'Another Shop',
            'slug' => 'existing-slug',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_shop(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($admin)->put("/admin/shops/{$shop->id}", [
            'name' => 'Updated Name',
            'slug' => $shop->slug,
            'description' => 'Updated description.',
            'is_active' => false,
        ]);

        $response->assertRedirect('/admin/shops');
        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_shop_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/admin/shops/{$shop->id}/toggle-status");

        $response->assertRedirect();
        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_shop(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/shops/{$shop->id}");

        $response->assertRedirect('/admin/shops');
        $this->assertDatabaseMissing('shops', [
            'id' => $shop->id,
        ]);
    }
}
