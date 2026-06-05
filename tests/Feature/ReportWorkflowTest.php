<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\ForecastService;
use App\Services\StockService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_director_can_open_reports_and_export_critical_products_as_pdf_and_excel(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $generalDirector = User::factory()->create();
        $generalDirector->assignRole('Directeur general');
        $operator = User::factory()->create();
        $product = Product::factory()->create(['critical_stock' => 5]);
        app(StockService::class)->enter($product, $operator, 3);
        app(ForecastService::class)->generateForProduct($product->refresh());

        $this->actingAs($generalDirector)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Produits critiques')
            ->assertSee('Prévisions de rupture');

        $pdf = $this->actingAs($generalDirector)->get(route('reports.export', [
            'report' => 'critical-products',
            'format' => 'pdf',
        ]));

        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $excel = $this->actingAs($generalDirector)->get(route('reports.export', [
            'report' => 'critical-products',
            'format' => 'excel',
        ]));

        $excel->assertOk();
        $this->assertStringContainsString(
            'produits-critiques-'.today()->format('Y-m-d').'.xlsx',
            $excel->headers->get('content-disposition'),
        );
    }

    public function test_warehouse_keeper_cannot_open_reports_or_export_them(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $warehouseKeeper = User::factory()->create();
        $warehouseKeeper->assignRole('Magasinier');

        $this->actingAs($warehouseKeeper)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($warehouseKeeper)->get(route('reports.export', [
            'report' => 'stock',
            'format' => 'pdf',
        ]))->assertForbidden();
    }
}
