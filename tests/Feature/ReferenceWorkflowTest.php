<?php

namespace Tests\Feature;

use App\Enums\RecordStatus;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_manager_can_create_update_and_archive_categories_and_suppliers(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post(route('categories.store'), [
            'name' => 'Materiel medical',
            'description' => 'Equipements et consommables.',
        ])->assertRedirect(route('categories.index'));

        $category = Category::query()->where('name', 'Materiel medical')->firstOrFail();

        $this->actingAs($manager)->put(route('categories.update', $category), [
            'name' => 'Consommables medicaux',
            'description' => 'Consommables uniquement.',
        ])->assertRedirect(route('categories.index'));

        $this->actingAs($manager)->patch(route('categories.archive', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertSame(RecordStatus::Archived, $category->refresh()->status);

        $this->actingAs($manager)->post(route('suppliers.store'), [
            'name' => 'Pharma Distribution',
            'contact_name' => 'Equipe commerciale',
            'phone' => '+229 01 91 00 00 00',
            'email' => 'contact@pharma.test',
            'address' => 'Cotonou',
        ])->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::query()->where('name', 'Pharma Distribution')->firstOrFail();

        $this->actingAs($manager)->patch(route('suppliers.archive', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertSame(RecordStatus::Archived, $supplier->refresh()->status);
    }

    public function test_archived_supplier_cannot_be_selected_for_a_new_product(): void
    {
        $manager = $this->manager();
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create(['status' => RecordStatus::Archived]);

        $this->actingAs($manager)->post(route('products.store'), [
            'category_id' => $category->getKey(),
            'supplier_id' => $supplier->getKey(),
            'name' => 'Produit refuse',
            'sku' => 'REFUSED-001',
            'purchase_price' => 100,
            'sale_price' => 120,
            'critical_stock' => 1,
            'unit' => 'unite',
        ])->assertSessionHasErrors('supplier_id');

        $this->assertDatabaseMissing('products', ['sku' => 'REFUSED-001']);
    }

    private function manager(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('Responsable stock');

        return $manager;
    }
}
