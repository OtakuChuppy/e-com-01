<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $price = fake()->numberBetween(200, 2000);

        return [
            'category_id' => Category::factory(),
            'sku' => 'SKU-'.Str::upper(fake()->bothify('??###')),
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'title' => ucwords($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'brand' => fake()->randomElement(['Kitchen', 'Home', 'Care', null]),
            'status' => 'published',
            'base_price' => $price,
            'compare_at_price' => fake()->optional(0.3)->numberBetween($price + 50, $price + 500),
            'cost_price' => fake()->optional()->numberBetween((int) ($price * 0.6), (int) ($price * 0.9)),
            'stock_quantity' => fake()->numberBetween(5, 100),
            'low_stock_threshold' => 5,
            'weight_grams' => fake()->optional()->numberBetween(200, 3000),
            'is_featured' => fake()->boolean(20),
            'is_demo' => false,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }
}
