<?php

namespace Tests\Feature;

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\Product;
use App\Models\User;
use App\Services\ForecastService;
use App\Services\StockService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertForecastWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_and_resolving_alert_preserves_its_history(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $product = Product::factory()->create(['critical_stock' => 5]);
        app(StockService::class)->enter($product, $manager, 3);
        $alert = Alert::query()->firstOrFail();

        $this->actingAs($manager)
            ->get(route('alerts.show', $alert))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Entrer du stock')
            ->assertSee('Faire un inventaire')
            ->assertDontSee('Marquer comme résolue');

        $this->assertSame(AlertStatus::Viewed, $alert->refresh()->status);

        $this->actingAs($manager)
            ->patch(route('alerts.resolve', $alert))
            ->assertRedirect(route('alerts.show', $alert));

        $this->assertSame(AlertStatus::Resolved, $alert->refresh()->status);
        $this->assertNotNull($alert->resolved_at);
        $this->assertDatabaseHas('alerts', ['id' => $alert->getKey()]);
    }

    public function test_alerts_index_separates_new_viewed_and_resolved_alerts(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');

        $newProduct = Product::factory()->create(['name' => 'Produit alerte nouvelle', 'critical_stock' => 5]);
        $viewedProduct = Product::factory()->create(['name' => 'Produit alerte consultée', 'critical_stock' => 5]);
        $resolvedProduct = Product::factory()->create(['name' => 'Produit alerte résolue', 'critical_stock' => 5]);

        app(StockService::class)->enter($newProduct, $manager, 3);
        app(StockService::class)->enter($viewedProduct, $manager, 3);
        app(StockService::class)->enter($resolvedProduct, $manager, 3);

        Alert::query()
            ->whereBelongsTo($viewedProduct)
            ->firstOrFail()
            ->update(['status' => AlertStatus::Viewed]);

        Alert::query()
            ->whereBelongsTo($resolvedProduct)
            ->firstOrFail()
            ->update(['status' => AlertStatus::Resolved, 'resolved_at' => now()]);

        $this->actingAs($manager)
            ->get(route('alerts.index'))
            ->assertOk()
            ->assertSee('Alertes par produit')
            ->assertSee('Produits concernés')
            ->assertSee('Nouvelles alertes')
            ->assertSee('Alertes consultées')
            ->assertSee('Alertes résolues')
            ->assertSee('Produit alerte nouvelle')
            ->assertSee('Produit alerte consultée')
            ->assertSee('Produit alerte résolue');
    }

    public function test_stock_manager_can_recalculate_forecasts_on_demand(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');
        $product = Product::factory()->create(['critical_stock' => 5]);
        $stockService = app(StockService::class);
        $stockService->enter($product, $manager, 570);
        $stockService->exit($product, $manager, 450);

        $this->actingAs($manager)
            ->post(route('forecasts.refresh'))
            ->assertRedirect(route('forecasts.index'));

        $this->assertSame(1, $product->forecasts()->count());
        $forecast = $product->forecasts()->firstOrFail();
        $this->assertSame('5.0000', $forecast->average_daily_usage);

        $this->actingAs($manager)
            ->get(route('forecasts.show', $forecast))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Quantité recommandée')
            ->assertSee('Modéré')
            ->assertDontSee('50/100');
    }

    public function test_warehouse_keeper_cannot_open_alerts_or_forecasts(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');

        $this->actingAs($warehouseKeeper)->get(route('dashboard'))->assertRedirect(route('products.index'));
        $this->actingAs($warehouseKeeper)->get(route('alerts.index'))->assertForbidden();
        $this->actingAs($warehouseKeeper)->get(route('forecasts.index'))->assertForbidden();
    }
}
