<?php

namespace Database\Seeders;

use App\Models\Auth\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $tenant = Tenant::create(['id' => 'admin']);
        // $tenant->domains()->create(['domain' => 'admin.ravel']);

        $insert = [
            [
                'id' => 'admin',
                'tenant_type_id' => 1,
                'nome' => 'Administrador',
            ],
            [
                'id' => 'gpu-online-api',
                'tenant_type_id' => 2,
                'nome' => 'Gpu Online API',
            ],
            [
                'id' => 'inteligencia',
                'tenant_type_id' => 4,
                'nome' => 'Inteligência',
            ],
            [
                'id' => 'cdpamericana',
                'tenant_type_id' => 3,
                'nome' => 'CDP Americana',
            ],
        ];

        foreach ($insert as $data) {
            Tenant::create($data);
        }
    }
}
