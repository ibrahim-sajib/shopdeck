<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_global_products_list(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertOk();
    }

    public function test_shop_owner_cannot_access_admin_product_management(): void
    {
        $owner = User::factory()->shopOwner()->create();

        $response = $this->actingAs($owner)->get('/admin/products');

        $response->assertForbidden();
    }

    public function test_admin_can_create_shared_catalog_product(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Premium Wireless Headphones',
            'sku' => 'HD-PREM-001',
            'description' => 'High fidelity noise cancelling headphones.',
            'thumbnail' => UploadedFile::fake()->image('headphone.jpg'),
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Premium Wireless Headphones',
            'sku' => 'HD-PREM-001',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $product = Product::where('sku', 'HD-PREM-001')->first();
        $this->assertNotNull($product->thumbnail);
        Storage::disk('public')->assertExists($product->thumbnail);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['sku' => 'SKU-DUPE']);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Duplicate SKU Product',
            'sku' => 'SKU-DUPE',
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Old Title']);

        $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name' => 'New Title',
            'sku' => $product->sku,
            'description' => 'Updated global description.',
            'is_active' => false,
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Title',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/admin/products/{$product->id}/toggle-status");

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
