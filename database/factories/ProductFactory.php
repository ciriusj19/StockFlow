<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'name' => fake()->words(3, true),
            'sku' => fake()->unique()->bothify('SKU-####-??'),
            'barcode' => fake()->unique()->ean13(),
            'purchase_price' => 1000,
            'sale_price' => 1500,
            'critical_stock' => 5,
            'unit' => 'unite',
            'status' => RecordStatus::Active,
        ];
    }
}
