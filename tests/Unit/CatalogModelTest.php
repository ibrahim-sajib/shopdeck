<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_a_shop(): void
    {
        $user = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->create(['owner_id' => $user->id]);

        $this->assertTrue($user->shop->is($shop));
        $this->assertTrue($shop->owner->is($user));
    }

    public function test_product_belongs_to_creator(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $this->assertTrue($product->creator->is($admin));
        $this->assertTrue($admin->createdProducts->contains($product));
    }

    public function test_shop_and_product_many_to_many_relationship(): void
    {
        $shop = Shop::factory()->create();
        $product = Product::factory()->create(['name' => 'Original T-Shirt']);

        $listing = ShopProduct::factory()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 299.50,
            'name_override' => 'Custom Shop T-Shirt',
        ]);

        $this->assertTrue($shop->products->contains($product));
        $this->assertTrue($product->shops->contains($shop));
        $this->assertEquals('Custom Shop T-Shirt', $listing->resolved_name);
        $this->assertEquals(299.50, $listing->price);
    }

    public function test_duplicate_shop_product_pair_is_rejected_by_unique_constraint(): void
    {
        $shop = Shop::factory()->create();
        $product = Product::factory()->create();

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 100.00,
        ]);

        $this->expectException(QueryException::class);

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 150.00,
        ]);
    }

    public function test_resolved_attributes_fallback_to_global_product_when_no_override(): void
    {
        $product = Product::factory()->create([
            'name' => 'Base Product Name',
            'description' => 'Base Description',
            'thumbnail' => 'products/base.jpg',
        ]);

        $listing = ShopProduct::factory()->create([
            'product_id' => $product->id,
            'name_override' => null,
            'description_override' => null,
            'thumbnail_override' => null,
        ]);

        $this->assertEquals('Base Product Name', $listing->resolved_name);
        $this->assertEquals('Base Description', $listing->resolved_description);
        $this->assertEquals('products/base.jpg', $listing->resolved_thumbnail);
    }
}
