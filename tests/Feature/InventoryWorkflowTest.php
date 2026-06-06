<?php

namespace Tests\Feature;

use App\Enums\AlertStatus;
use App\Enums\InventoryStatus;
use App\Enums\MovementType;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_manager_can_create_count_and_validate_an_inventory(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $product = Product::factory()->create(['critical_stock' => 5]);
        app(StockService::class)->enter($product, $manager, 10);

        $this->actingAs($manager)->post(route('inventories.store'), [
            'inventory_date' => today()->toDateString(),
            'product_ids' => [$product->getKey()],
        ])->assertRedirect();

        $inventory = Inventory::query()->firstOrFail();
        $item = $inventory->items()->firstOrFail();

        $this->assertSame('10.00', $item->expected_quantity);
        $this->assertSame('10.00', $item->actual_quantity);

        $this->actingAs($manager)->put(route('inventories.update', $inventory), [
            'actual_quantities' => [$item->getKey() => 4],
            'notes' => 'Comptage physique',
        ])->assertRedirect(route('inventories.show', $inventory));

        $this->actingAs($manager)->post(route('inventories.validate', $inventory))
            ->assertRedirect(route('inventories.show', $inventory));

        $this->assertSame(InventoryStatus::Validated, $inventory->refresh()->status);
        $this->assertSame('4.00', $product->refresh()->current_stock);
        $this->assertSame(1, StockMovement::query()->where('type', MovementType::Adjustment->value)->count());
        $this->assertSame(1, $product->alerts()->where('status', AlertStatus::New->value)->count());
    }

    public function test_validated_inventory_cannot_be_edited(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $inventory = Inventory::query()->create([
            'user_id' => $manager->getKey(),
            'inventory_date' => today(),
            'status' => InventoryStatus::Validated,
            'validated_at' => now(),
        ]);

        $this->actingAs($manager)->put(route('inventories.update', $inventory), [
            'actual_quantities' => [],
        ])->assertForbidden();
    }

    public function test_inventory_creation_can_preselect_a_product_from_an_alert(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $product = Product::factory()->create(['name' => 'Produit a inventorier']);

        $this->actingAs($manager)
            ->get(route('inventories.create', ['product_id' => $product->getKey()]))
            ->assertOk()
            ->assertSee('Produit a inventorier')
            ->assertSee('value="'.$product->getKey().'" checked', false);
    }

    public function test_warehouse_keeper_cannot_create_inventory(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');

        $this->actingAs($warehouseKeeper)
            ->get(route('inventories.create'))
            ->assertForbidden();
    }
}
