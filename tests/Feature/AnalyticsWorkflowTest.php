<?php

namespace Tests\Feature;

use App\Models\AnalyticsRun;
use App\Models\Product;
use App\Models\User;
use App\Services\AnalyticsCompilationService;
use App\Services\StockService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_chief_can_compile_view_and_export_decision_summary(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $agencyChief = User::factory()->create();
        $agencyChief->assignRole("Chef d'agence");
        $operator = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Insuline rapide',
            'critical_stock' => 10,
            'current_stock' => 8,
        ]);

        app(StockService::class)->enter($product, $operator, 40);
        app(StockService::class)->exit($product->refresh(), $operator, 32);

        $this->actingAs($agencyChief)
            ->post(route('analytics.compile'))
            ->assertRedirect(route('analytics.index'));

        $this->assertSame(1, AnalyticsRun::query()->count());
        $run = AnalyticsRun::query()->firstOrFail();
        $this->assertTrue($run->is_current);
        $this->assertGreaterThan(0, $run->productKpis()->count());
        $this->assertArrayHasKey('data_quality_score', $run->summary);
        $this->assertArrayHasKey('products_without_consumption_count', $run->summary);
        $this->assertArrayHasKey('non_estimable_forecasts_count', $run->summary);
        $this->assertArrayHasKey('critical_stock_products_count', $run->summary);
        $this->assertArrayHasKey('inventory_sensitive_products_count', $run->summary);

        $this->actingAs($agencyChief)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('Synthèse décisionnelle')
            ->assertSee('Qualité des données')
            ->assertSee('Sources utilisées')
            ->assertSee('Insuline rapide');

        $this->actingAs($agencyChief)
            ->get(route('analytics.export', ['format' => 'excel']))
            ->assertOk();
    }

    public function test_decision_levels_are_separated_by_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');
        $stockManager = User::factory()->create();
        $stockManager->assignRole('Responsable stock');
        $generalDirector = User::factory()->create();
        $generalDirector->assignRole('Directeur general');

        $this->actingAs($warehouseKeeper)->get(route('analytics.index'))->assertForbidden();
        $this->actingAs($stockManager)->get(route('analytics.index'))->assertForbidden();
        $this->actingAs($generalDirector)->get(route('analytics.index'))->assertOk();
        $this->actingAs($generalDirector)->post(route('analytics.compile'))->assertForbidden();
    }

    public function test_compilation_exposes_chart_series_as_lists(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Administrateur');
        Product::factory()->count(3)->create();

        $service = app(AnalyticsCompilationService::class);
        $run = $service->compile($user);
        $data = $service->dashboardData($run);

        $this->assertTrue(array_is_list($data['charts']['category_risk']['labels']));
        $this->assertTrue(array_is_list($data['charts']['category_risk']['series']));
        $this->assertTrue(array_is_list($data['charts']['supplier_dependency']['labels']));
        $this->assertTrue(array_is_list($data['charts']['supplier_dependency']['series']));
        $this->assertTrue(array_is_list($data['charts']['data_quality']['labels']));
        $this->assertTrue(array_is_list($data['charts']['data_quality']['series']));
        $this->assertArrayHasKey('runHistory', $data);
    }
}
