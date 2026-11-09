<?php

namespace Tests\Feature\Owner;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_own_listings(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        ShopProduct::factory()->count(2)->create(['shop_id' => $shop->id]);

        $response = $this->actingAs($owner)->get('/owner/products');

        $response->assertOk();
    }

    public function test_owner_can_create_own_product_with_transactional_listing(): void
    {
        Storage::fake('public');
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post('/owner/products', [
            'name' => 'Owner Custom Bag',
            'sku' => 'BAG-OWN-01',
            'description' => 'A unique leather bag crafted by owner.',
            'thumbnail' => UploadedFile::fake()->image('bag.jpg'),
            'price' => 1250.00,
        ]);

        $response->assertRedirect('/owner/products');

        // Check global product was created
        $this->assertDatabaseHas('products', [
            'name' => 'Owner Custom Bag',
            'sku' => 'BAG-OWN-01',
            'created_by' => $owner->id,
            'is_active' => true,
        ]);

        $product = Product::where('sku', 'BAG-OWN-01')->first();
        $this->assertNotNull($product->thumbnail);

        // Check shop listing was created in transaction
        $this->assertDatabaseHas('shop_products', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 1250.00,
            'is_active' => true,
        ]);
    }

    public function test_owner_can_update_own_listing_overrides_and_price(): void
    {
        Storage::fake('public');
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        $product = Product::factory()->create();

        $listing = ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 500.00,
        ]);

        $response = $this->actingAs($owner)->put("/owner/products/{$listing->id}", [
            'price' => 799.99,
            'name_override' => 'Mega Discounted Title',
            'description_override' => 'Custom sales description for my shop.',
            'thumbnail_override' => UploadedFile::fake()->image('custom_thumb.jpg'),
            'is_active' => true,
        ]);

        $response->assertRedirect('/owner/products');

        $this->assertDatabaseHas('shop_products', [
            'id' => $listing->id,
            'price' => 799.99,
            'name_override' => 'Mega Discounted Title',
            'description_override' => 'Custom sales description for my shop.',
        ]);

        $updatedListing = $listing->fresh();
        $this->assertNotNull($updatedListing->thumbnail_override);
        Storage::disk('public')->assertExists($updatedListing->thumbnail_override);
    }

    public function test_owner_cannot_update_another_shops_listing(): void
    {
        $owner1 = User::factory()->shopOwner()->create();
        $shop1 = Shop::factory()->create(['owner_id' => $owner1->id]);

        $owner2 = User::factory()->shopOwner()->create();
        $shop2 = Shop::factory()->create(['owner_id' => $owner2->id]);

        $listingInShop2 = ShopProduct::factory()->create([
            'shop_id' => $shop2->id,
            'price' => 300.00,
        ]);

        $response = $this->actingAs($owner1)->put("/owner/products/{$listingInShop2->id}", [
            'price' => 100.00,
        ]);

        $response->assertForbidden();
        $this->assertEquals(300.00, $listingInShop2->fresh()->price);
    }

    public function test_owner_cannot_delete_another_shops_listing(): void
    {
        $owner1 = User::factory()->shopOwner()->create();
        $shop1 = Shop::factory()->create(['owner_id' => $owner1->id]);

        $owner2 = User::factory()->shopOwner()->create();
        $shop2 = Shop::factory()->create(['owner_id' => $owner2->id]);

        $listingInShop2 = ShopProduct::factory()->create([
            'shop_id' => $shop2->id,
        ]);

        $response = $this->actingAs($owner1)->delete("/owner/products/{$listingInShop2->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('shop_products', ['id' => $listingInShop2->id]);
    }

    public function test_owner_can_toggle_listing_status(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        $listing = ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->post("/owner/products/{$listing->id}/toggle-status");

        $response->assertRedirect();
        $this->assertDatabaseHas('shop_products', [
            'id' => $listing->id,
            'is_active' => false,
        ]);
    }

    public function test_owner_can_remove_listing_from_shop(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $owner->id]);
        $product = Product::factory()->create();

        $listing = ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($owner)->delete("/owner/products/{$listing->id}");

        $response->assertRedirect('/owner/products');
        $this->assertDatabaseMissing('shop_products', ['id' => $listing->id]);
        // Global product is preserved
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
