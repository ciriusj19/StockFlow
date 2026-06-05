<?php

namespace Tests\Feature;

use App\Enums\AlertStatus;
use App\Enums\MovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_manager_can_create_a_product_and_record_entry_and_exit_movements(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($manager)->post(route('products.store'), [
            'category_id' => $category->getKey(),
            'supplier_id' => $supplier->getKey(),
            'name' => 'Gants medicaux',
            'sku' => 'MED-GANTS-100',
            'barcode' => '3400930000097',
            'purchase_price' => 2000,
            'sale_price' => 2800,
            'critical_stock' => 10,
            'unit' => 'boite',
        ]);

        $product = Product::query()->where('sku', 'MED-GANTS-100')->firstOrFail();
        $response->assertRedirect(route('products.show', $product));
        $this->assertSame('0.00', $product->current_stock);

        $this->actingAs($manager)->post(route('products.movements.store', $product), [
            'type' => 'ENTRY',
            'quantity' => 30,
            'reference' => 'BL-100',
        ])->assertRedirect(route('products.show', $product));

        $this->actingAs($manager)->post(route('products.movements.store', $product), [
            'type' => 'EXIT',
            'quantity' => 22,
            'reference' => 'BS-100',
        ])->assertRedirect(route('products.show', $product));

        $this->assertSame('8.00', $product->refresh()->current_stock);
        $this->assertSame(2, $product->stockMovements()->count());
        $this->assertSame(1, $product->alerts()->count());
    }

    public function test_warehouse_keeper_can_record_movements_but_cannot_create_products(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');
        $product = Product::factory()->create();

        $this->actingAs($warehouseKeeper)
            ->get(route('products.create'))
            ->assertForbidden();

        $this->actingAs($warehouseKeeper)->post(route('products.movements.store', $product), [
            'type' => 'ENTRY',
            'quantity' => 12,
        ])->assertRedirect(route('products.show', $product));

        $this->assertSame('12.00', $product->refresh()->current_stock);

        $this->actingAs($warehouseKeeper)
            ->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Risque');

        $this->actingAs($warehouseKeeper)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Risque')
            ->assertDontSee('Ajustement de stock');
    }

    public function test_stock_manager_can_adjust_stock_directly_but_warehouse_keeper_cannot(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');
        $product = Product::factory()->create([
            'current_stock' => 20,
            'critical_stock' => 10,
        ]);

        $this->actingAs($manager)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Ajustement de stock');

        $this->actingAs($manager)->post(route('products.movements.store', $product), [
            'type' => 'ADJUSTMENT',
            'quantity' => 7,
            'reference' => 'CTRL-001',
            'notes' => 'Comptage ponctuel',
        ])->assertRedirect(route('products.show', $product));

        $adjustment = StockMovement::query()->where('type', MovementType::Adjustment->value)->firstOrFail();
        $this->assertSame('13.00', $adjustment->quantity);
        $this->assertSame('20.00', $adjustment->stock_before);
        $this->assertSame('7.00', $adjustment->stock_after);
        $this->assertSame('7.00', $product->refresh()->current_stock);
        $this->assertSame(1, $product->alerts()->where('status', AlertStatus::New->value)->count());

        $this->actingAs($manager)->post(route('products.movements.store', $product), [
            'type' => 'ADJUSTMENT',
            'quantity' => 0,
        ])->assertRedirect(route('products.show', $product));

        $this->assertSame('0.00', $product->refresh()->current_stock);
        $this->assertSame(2, $product->stockMovements()->where('type', MovementType::Adjustment->value)->count());

        $this->actingAs($warehouseKeeper)->post(route('products.movements.store', $product), [
            'type' => 'ADJUSTMENT',
            'quantity' => 5,
        ])->assertForbidden();
    }

    public function test_product_form_rejects_a_sale_price_lower_than_purchase_price(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($manager)->post(route('products.store'), [
            'category_id' => $category->getKey(),
            'supplier_id' => $supplier->getKey(),
            'name' => 'Produit invalide',
            'sku' => 'INVALID-001',
            'purchase_price' => 5000,
            'sale_price' => 4000,
            'critical_stock' => 2,
            'unit' => 'boite',
        ])->assertSessionHasErrors('sale_price');

        $this->assertDatabaseMissing('products', ['sku' => 'INVALID-001']);
    }
}
