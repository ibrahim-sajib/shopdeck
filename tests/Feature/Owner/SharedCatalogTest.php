<?php

namespace Tests\Feature\Owner;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_owner_can_browse_active_catalog_products(): void
    {
        $owner = User::factory()->shopOwner()->create();
        Shop::factory()->create(['owner_id' => $owner->id]);

        $activeProduct = Product::factory()->create(['name' => 'Active Item', 'is_active' => true]);
        $inactiveProduct = Product::factory()->create(['name' => 'Inactive Item', 'is_active' => false]);

        $response = $this->actingAs($owner)->get('/owner/catalog');

        $response->assertOk();
    }

    public function test_admin_cannot_access_owner_catalog(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/owner/catalog');

        $response->assertForbidden();
    }

    public function test_owner_can_add_catalog_product_to_own_shop(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->actingAs($owner)->post("/owner/catalog/{$product->id}/add", [
            'price' => 750.50,
            'name_override' => 'Custom Shop Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shop_products', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 750.50,
            'name_override' => 'Custom Shop Title',
        ]);
    }

    public function test_duplicate_add_to_same_shop_is_prevented(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        $product = Product::factory()->create(['is_active' => true]);

        ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 500.00,
        ]);

        $response = $this->actingAs($owner)->post("/owner/catalog/{$product->id}/add", [
            'price' => 600.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, ShopProduct::where('shop_id', $shop->id)->where('product_id', $product->id)->count());
    }

    public function test_owner_cannot_add_inactive_product(): void
    {
        $owner = User::factory()->shopOwner()->create();
        Shop::factory()->create(['owner_id' => $owner->id]);
        $product = Product::factory()->inactive()->create();

        $response = $this->actingAs($owner)->post("/owner/catalog/{$product->id}/add", [
            'price' => 500.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('shop_products', [
            'product_id' => $product->id,
        ]);
    }

    public function test_owner_without_assigned_shop_cannot_add_product(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->actingAs($owner)->post("/owner/catalog/{$product->id}/add", [
            'price' => 500.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('shop_products', [
            'product_id' => $product->id,
        ]);
    }
}
