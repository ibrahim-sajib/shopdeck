<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_global_product_can_be_listed_in_different_shops_with_different_prices(): void
    {
        $admin = User::factory()->admin()->create();
        $globalProduct = Product::factory()->create([
            'name' => 'Cotton Polo Shirt',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $ownerA = User::factory()->shopOwner()->create();
        $shopA = Shop::factory()->create(['name' => 'Shop A', 'slug' => 'shop-a', 'owner_id' => $ownerA->id]);

        $ownerB = User::factory()->shopOwner()->create();
        $shopB = Shop::factory()->create(['name' => 'Shop B', 'slug' => 'shop-b', 'owner_id' => $ownerB->id]);

        // Shop A lists at ৳650
        $listingA = ShopProduct::create([
            'shop_id' => $shopA->id,
            'product_id' => $globalProduct->id,
            'price' => 650.00,
            'name_override' => 'Polo Shirt - Shop A Edition',
            'is_active' => true,
        ]);

        // Shop B lists at ৳720
        $listingB = ShopProduct::create([
            'shop_id' => $shopB->id,
            'product_id' => $globalProduct->id,
            'price' => 720.00,
            'name_override' => 'Polo Shirt - Shop B Edition',
            'is_active' => true,
        ]);

        $this->assertEquals(650.00, $listingA->price);
        $this->assertEquals(720.00, $listingB->price);

        // Public view for Shop A
        $resA = $this->get('/shops/shop-a');
        $resA->assertOk();
        $resA->assertSee('Polo Shirt - Shop A Edition');
        $resA->assertSee('650.00');
        $resA->assertDontSee('720.00');

        // Public view for Shop B
        $resB = $this->get('/shops/shop-b');
        $resB->assertOk();
        $resB->assertSee('Polo Shirt - Shop B Edition');
        $resB->assertSee('720.00');
        $resB->assertDontSee('650.00');
    }

    public function test_updating_shop_a_does_not_affect_shop_b(): void
    {
        $globalProduct = Product::factory()->create(['name' => 'Shared Sneakers']);

        $ownerA = User::factory()->shopOwner()->create();
        $shopA = Shop::factory()->create(['owner_id' => $ownerA->id]);

        $ownerB = User::factory()->shopOwner()->create();
        $shopB = Shop::factory()->create(['owner_id' => $ownerB->id]);

        $listingA = ShopProduct::create([
            'shop_id' => $shopA->id,
            'product_id' => $globalProduct->id,
            'price' => 1500.00,
            'is_active' => true,
        ]);

        $listingB = ShopProduct::create([
            'shop_id' => $shopB->id,
            'product_id' => $globalProduct->id,
            'price' => 1800.00,
            'is_active' => true,
        ]);

        // Owner A updates their listing price to ৳1200
        $this->actingAs($ownerA)->put("/owner/products/{$listingA->id}", [
            'price' => 1200.00,
            'name_override' => 'Discounted Sneakers',
            'is_active' => true,
        ]);

        $this->assertEquals(1200.00, $listingA->fresh()->price);
        $this->assertEquals('Discounted Sneakers', $listingA->fresh()->name_override);

        // Shop B listing remains intact
        $this->assertEquals(1800.00, $listingB->fresh()->price);
        $this->assertNull($listingB->fresh()->name_override);
    }

    public function test_owner_a_created_product_can_be_discovered_and_listed_by_owner_b(): void
    {
        $ownerA = User::factory()->shopOwner()->create();
        $shopA = Shop::factory()->create(['owner_id' => $ownerA->id]);

        $ownerB = User::factory()->shopOwner()->create();
        $shopB = Shop::factory()->create(['owner_id' => $ownerB->id]);

        // Owner A creates product
        $this->actingAs($ownerA)->post('/owner/products', [
            'name' => 'Artisan Leather Belt',
            'sku' => 'BELT-ART-01',
            'price' => 800.00,
        ]);

        $product = Product::where('sku', 'BELT-ART-01')->first();
        $this->assertNotNull($product);

        // Owner B browses catalog and sees Owner A's product
        $resCatalog = $this->actingAs($ownerB)->get('/owner/catalog');
        $resCatalog->assertOk();

        // Owner B adds product to Shop B with different price
        $this->actingAs($ownerB)->post("/owner/catalog/{$product->id}/add", [
            'price' => 950.00,
        ]);

        $this->assertDatabaseHas('shop_products', [
            'shop_id' => $shopB->id,
            'product_id' => $product->id,
            'price' => 950.00,
        ]);
    }

    public function test_negative_or_invalid_price_is_rejected(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        $product = Product::factory()->create();

        // Negative price on add to shop
        $response = $this->actingAs($owner)->post("/owner/catalog/{$product->id}/add", [
            'price' => -50.00,
        ]);

        $response->assertSessionHasErrors('price');

        // Non-numeric price on create product
        $responseCreate = $this->actingAs($owner)->post('/owner/products', [
            'name' => 'Test Product',
            'price' => 'invalid-price',
        ]);

        $responseCreate->assertSessionHasErrors('price');
    }

    public function test_invalid_thumbnail_file_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $invalidFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Product with PDF',
            'thumbnail' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('thumbnail');
    }
}
