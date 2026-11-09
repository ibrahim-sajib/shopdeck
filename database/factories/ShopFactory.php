<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = fake()->unique()->company() . ' Shop';

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraph(),
            'logo' => null,
            'owner_id' => User::factory()->shopOwner(),
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
