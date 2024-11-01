<?php

namespace Database\Seeders;

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
            TenantSeeder::class,
            UserSeeder::class,
            TenantUserSeeder::class,
            PermissionModuleSeeder::class,
            PermissionGroupSeeder::class,
            PermissionSeeder::class,
            PermissionConfigSeeder::class,

            PessoaTipoTabelaSeeder::class,
            InformacaoSubjetivaCategoriaSeeder::class,
        ]);
    }
}
