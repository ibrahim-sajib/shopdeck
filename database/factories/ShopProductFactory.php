<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopProduct>
 */
class ShopProductFactory extends Factory
{
    protected $model = ShopProduct::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'product_id' => Product::factory(),
            'price' => fake()->randomFloat(2, 50, 1000),
            'name_override' => null,
            'description_override' => null,
            'thumbnail_override' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
