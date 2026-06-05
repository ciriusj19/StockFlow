<?php

namespace Database\Seeders;

use App\Enums\AlertStatus;
use App\Enums\InventoryStatus;
use App\Enums\MovementType;
use App\Models\Alert;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AnalyticsCompilationService;
use App\Services\ForecastService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@stockflow.local')->first();

        if (! $admin) {
            return;
        }

        DB::transaction(function () use ($admin) {
            $categories = collect(['Medicaments', 'Consommables', 'Materiel medical', 'Hygiene', 'Diagnostic'])
                ->mapWithKeys(fn ($name) => [$name => Category::query()->updateOrCreate(
                    ['name' => $name],
                    ['description' => 'Categorie '.$name, 'status' => 'active'],
                )]);

            $suppliers = collect(['Pharma Benin', 'MedSupply Afrique', 'Clinique Equipements', 'Hygia Distribution'])
                ->mapWithKeys(fn ($name) => [$name => Supplier::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'contact_name' => 'Contact '.$name,
                        'phone' => '+229 01 00 00 00 00',
                        'email' => Str::of($name)->lower()->replace(' ', '.').'@stockflow.local',
                        'address' => 'Cotonou',
                        'status' => 'active',
                    ],
                )]);

            $products = [
                ['Paracetamol 500 mg', 'MED-PARA-500', 'Medicaments', 'Pharma Benin', 18, 35, 20, 'boite'],
                ['Amoxicilline 500 mg', 'MED-AMOX-500', 'Medicaments', 'Pharma Benin', 34, 30, 18, 'boite'],
                ['Insuline rapide', 'MED-INS-RAP', 'Medicaments', 'MedSupply Afrique', 62, 15, 10, 'flacon'],
                ['Gants nitrile', 'CON-GANT-NIT', 'Consommables', 'MedSupply Afrique', 11, 60, 40, 'boite'],
                ['Masques chirurgicaux', 'CON-MASQ-CHI', 'Consommables', 'MedSupply Afrique', 8, 90, 55, 'boite'],
                ['Compresses steriles', 'CON-COMP-STR', 'Consommables', 'Hygia Distribution', 6, 22, 25, 'paquet'],
                ['Serum physiologique', 'MED-SER-PHY', 'Materiel medical', 'Clinique Equipements', 14, 24, 18, 'flacon'],
                ['Seringues 5 ml', 'MAT-SER-5ML', 'Materiel medical', 'Clinique Equipements', 3, 80, 50, 'unite'],
                ['Thermometres digitaux', 'MAT-THER-DIG', 'Materiel medical', 'Clinique Equipements', 9, 12, 8, 'unite'],
                ['Gel hydroalcoolique', 'HYG-GEL-HYD', 'Hygiene', 'Hygia Distribution', 10, 45, 30, 'flacon'],
                ['Desinfectant surface', 'HYG-DES-SUR', 'Hygiene', 'Hygia Distribution', 12, 9, 14, 'bidon'],
                ['Bandelettes glycemie', 'DIA-BAND-GLY', 'Diagnostic', 'Pharma Benin', 16, 8, 12, 'boite'],
            ];

            foreach ($products as [$name, $sku, $category, $supplier, $price, $stock, $critical, $unit]) {
                Product::query()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'category_id' => $categories[$category]->getKey(),
                        'supplier_id' => $suppliers[$supplier]->getKey(),
                        'name' => $name,
                        'barcode' => null,
                        'purchase_price' => $price,
                        'sale_price' => $price * 1.35,
                        'current_stock' => $stock,
                        'critical_stock' => $critical,
                        'unit' => $unit,
                        'status' => 'active',
                    ],
                );
            }

            Product::query()->each(function (Product $product) use ($admin) {
                StockMovement::query()->where('product_id', $product->getKey())->delete();

                StockMovement::query()->create([
                    'product_id' => $product->getKey(),
                    'user_id' => $admin->getKey(),
                    'type' => MovementType::Entry,
                    'quantity' => (float) $product->current_stock + 40,
                    'stock_before' => 0,
                    'stock_after' => (float) $product->current_stock + 40,
                    'reference' => 'DEMO-ENTRY',
                    'notes' => 'Stock de demonstration',
                    'movement_date' => now()->subDays(75),
                ]);

                foreach ([55, 35, 15] as $daysAgo) {
                    $quantity = max(1, min(18, (float) $product->critical_stock / 2));
                    StockMovement::query()->create([
                        'product_id' => $product->getKey(),
                        'user_id' => $admin->getKey(),
                        'type' => MovementType::Exit,
                        'quantity' => $quantity,
                        'stock_before' => (float) $product->current_stock + $quantity,
                        'stock_after' => (float) $product->current_stock,
                        'reference' => 'DEMO-EXIT',
                        'notes' => 'Consommation de demonstration',
                        'movement_date' => now()->subDays($daysAgo),
                    ]);
                }

                if ((float) $product->current_stock <= (float) $product->critical_stock) {
                    Alert::query()->updateOrCreate(
                        ['product_id' => $product->getKey(), 'status' => AlertStatus::New],
                        [
                            'type' => 'critical_stock',
                            'message' => 'Le stock de '.$product->name.' a atteint le seuil critique.',
                            'triggered_at' => now()->subDays(2),
                        ],
                    );
                }
            });

            $inventory = Inventory::query()->updateOrCreate(
                ['notes' => 'Inventaire de demonstration'],
                [
                    'user_id' => $admin->getKey(),
                    'inventory_date' => now()->subDays(3)->toDateString(),
                    'status' => InventoryStatus::Validated,
                    'validated_at' => now()->subDays(3),
                ],
            );

            Product::query()->take(6)->get()->each(function (Product $product, int $index) use ($inventory) {
                $difference = [-4, 6, -2, 5, -7, 3][$index] ?? 0;
                $inventory->items()->updateOrCreate(
                    ['product_id' => $product->getKey()],
                    [
                        'expected_quantity' => (float) $product->current_stock - $difference,
                        'actual_quantity' => (float) $product->current_stock,
                        'difference' => $difference,
                    ],
                );
            });
        });

        app(ForecastService::class)->generateAll();
        app(AnalyticsCompilationService::class)->compile($admin);
    }
}
