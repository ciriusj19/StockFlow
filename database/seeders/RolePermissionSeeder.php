<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'analytics.view',
            'analytics.compile',
            'analytics.export',
            'users.view',
            'users.create',
            'users.update',
            'users.disable',
            'roles.view',
            'roles.manage',
            'products.view',
            'products.create',
            'products.update',
            'products.archive',
            'categories.view',
            'categories.create',
            'categories.update',
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'stock.view',
            'stock.entry',
            'stock.exit',
            'stock.adjustment',
            'inventories.view',
            'inventories.create',
            'inventories.validate',
            'alerts.view',
            'alerts.resolve',
            'reports.view',
            'reports.export',
            'forecasts.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $administrator = Role::findOrCreate('Administrateur', 'web');
        $responsableStock = Role::findOrCreate('Responsable stock', 'web');
        $legacyManager = Role::findOrCreate('Gestionnaire', 'web');
        $warehouseKeeper = Role::findOrCreate('Magasinier', 'web');
        $agencyChief = Role::findOrCreate("Chef d'agence", 'web');
        $generalDirector = Role::findOrCreate('Directeur general', 'web');

        $administrator->syncPermissions($permissions);
        $responsablePermissions = [
            'products.view',
            'products.create',
            'products.update',
            'products.archive',
            'categories.view',
            'categories.create',
            'categories.update',
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'stock.view',
            'stock.entry',
            'stock.exit',
            'stock.adjustment',
            'inventories.view',
            'inventories.create',
            'inventories.validate',
            'alerts.view',
            'alerts.resolve',
            'forecasts.view',
            'roles.view',
        ];

        $responsableStock->syncPermissions($responsablePermissions);
        $legacyManager->syncPermissions($responsablePermissions);
        $warehouseKeeper->syncPermissions([
            'products.view',
            'stock.view',
            'stock.entry',
            'stock.exit',
        ]);
        $agencyChief->syncPermissions([
            'analytics.view',
            'analytics.compile',
            'analytics.export',
        ]);
        $generalDirector->syncPermissions([
            'analytics.view',
            'analytics.export',
            'reports.view',
            'reports.export',
        ]);

        $legacyManager->users()->each(function ($user) use ($responsableStock): void {
            $user->assignRole($responsableStock);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
