<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $administrator = User::query()->updateOrCreate([
            'email' => 'admin@stockflow.local',
        ], [
            'name' => 'Administrateur StockFlow',
            'password' => 'password',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $administrator->syncRoles(['Administrateur']);

        $this->call(DemoDataSeeder::class);
    }
}
