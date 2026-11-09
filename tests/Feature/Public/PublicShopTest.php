<?php

namespace Tests\Feature\Public;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_active_public_shop(): void
    {
        $shop = Shop::factory()->create([
            'name' => 'Sunset Boutique',
            'slug' => 'sunset-boutique',
            'is_active' => true,
        ]);

        $response = $this->get('/shops/sunset-boutique');

        $response->assertOk();
    }

    public function test_inactive_shop_returns_404(): void
    {
        $shop = Shop::factory()->inactive()->create([
            'slug' => 'inactive-shop',
        ]);

        $response = $this->get('/shops/inactive-shop');

        $response->assertNotFound();
    }

    public function test_unknown_shop_slug_returns_404(): void
    {
        $response = $this->get('/shops/non-existent-shop-slug');

        $response->assertNotFound();
    }

    public function test_only_assigned_active_products_are_visible_in_shop(): void
    {
        $shopA = Shop::factory()->create(['slug' => 'shop-a', 'is_active' => true]);
        $shopB = Shop::factory()->create(['slug' => 'shop-b', 'is_active' => true]);

        $productA = Product::factory()->create(['name' => 'Exclusive to Shop A', 'is_active' => true]);
        $productB = Product::factory()->create(['name' => 'Exclusive to Shop B', 'is_active' => true]);
        $unassignedProduct = Product::factory()->create(['name' => 'Unassigned Global Item', 'is_active' => true]);

        ShopProduct::factory()->create([
            'shop_id' => $shopA->id,
            'product_id' => $productA->id,
            'price' => 500.00,
            'is_active' => true,
        ]);

        ShopProduct::factory()->create([
            'shop_id' => $shopB->id,
            'product_id' => $productB->id,
            'price' => 700.00,
            'is_active' => true,
        ]);

        // Customer visits Shop A
        $responseA = $this->get('/shops/shop-a');
        $responseA->assertOk();
        $responseA->assertSee('Exclusive to Shop A');
        $responseA->assertDontSee('Exclusive to Shop B');
        $responseA->assertDontSee('Unassigned Global Item');

        // Customer visits Shop B
        $responseB = $this->get('/shops/shop-b');
        $responseB->assertOk();
        $responseB->assertSee('Exclusive to Shop B');
        $responseB->assertDontSee('Exclusive to Shop A');
        $responseB->assertDontSee('Unassigned Global Item');
    }

    public function test_inactive_listing_is_hidden(): void
    {
        $shop = Shop::factory()->create(['slug' => 'hidden-test-shop', 'is_active' => true]);
        $product = Product::factory()->create(['name' => 'Draft Product', 'is_active' => true]);

        ShopProduct::factory()->inactive()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 200.00,
        ]);

        $response = $this->get('/shops/hidden-test-shop');
        $response->assertOk();
        $response->assertDontSee('Draft Product');
    }

    public function test_inactive_global_product_is_hidden_even_if_listing_is_active(): void
    {
        $shop = Shop::factory()->create(['slug' => 'catalog-test-shop', 'is_active' => true]);
        $product = Product::factory()->inactive()->create(['name' => 'Deactivated Global Product']);

        ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        $response = $this->get('/shops/catalog-test-shop');
        $response->assertOk();
        $response->assertDontSee('Deactivated Global Product');
    }

    public function test_shop_specific_overrides_are_displayed(): void
    {
        $shop = Shop::factory()->create(['slug' => 'override-shop', 'is_active' => true]);
        $product = Product::factory()->create([
            'name' => 'Base Global Name',
            'description' => 'Base Description',
            'is_active' => true,
        ]);

        ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 888.00,
            'name_override' => 'Custom Shop Override Name',
            'description_override' => 'Custom Shop Override Description',
            'is_active' => true,
        ]);

        $response = $this->get('/shops/override-shop');
        $response->assertOk();
        $response->assertSee('Custom Shop Override Name');
        $response->assertSee('888.00');
    }
}
