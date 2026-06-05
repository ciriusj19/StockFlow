<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockFlowWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_sees_dashboard_navigation(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrateur');

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord')
            ->assertSee('Produits')
            ->assertSee('Alertes')
            ->assertSee('Prévisions')
            ->assertSee('Synthèse décisionnelle')
            ->assertSee('Utilisateurs')
            ->assertSee('Rôles');
    }

    public function test_warehouse_keeper_gets_operational_navigation_only(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');

        $this->actingAs($warehouseKeeper)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Produits')
            ->assertDontSee('Alertes')
            ->assertDontSee('Prévisions')
            ->assertDontSee('Rapports')
            ->assertDontSee('Synthèse décisionnelle');
    }
}
