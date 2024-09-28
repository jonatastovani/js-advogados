<?php

namespace Database\Seeders;

use Database\Seeders\Referencia\ContaStatusTipoSeeder;
use Database\Seeders\Referencia\ContaSubtipoSeeder;
use Database\Seeders\Referencia\ContaTipoSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            TenantTypeSeeder::class,
            TenantDomainSeeder::class,
            UserSeeder::class,
            UserTenantDomainSeeder::class,
            PermissionModuleSeeder::class,
            PermissionGroupSeeder::class,
            PermissionSeeder::class,
            PermissionConfigSeeder::class,

            ContaStatusTipoSeeder::class,
            ContaTipoSeeder::class,
            ContaSubtipoSeeder::class,
        ]);
    }
}
