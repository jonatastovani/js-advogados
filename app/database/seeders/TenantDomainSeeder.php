<?php

namespace Database\Seeders;

use App\Models\Auth\Tenant;
use Illuminate\Database\Seeder;

class TenantDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::create([
            'id' => 'admin',
            'tenant_type_id' => 1,
            'name' => 'Administrador',
        ]);
        $tenant->domains()->create(['id' => 1, 'domain' => 'admin.byteforge.com.br', 'name' => 'Admin']);

        $tenant = Tenant::create([
            'id' => 'jsadvogados',
            'tenant_type_id' => 3,
            'name' => 'JS-Advogados',
        ]);
        $tenant->domains()->create(['id' => 2, 'domain' => 'jsadvogadosmc.byteforge.com.br', 'name' => 'Monte Castelo']);
        $tenant->domains()->create(['id' => 3, 'domain' => 'jsadvogadosprev.byteforge.com.br', 'name' => 'Prev']);

        // $tenant = Tenant::create([
        //     'id' => 'advocaciateste',
        //     'tenant_type_id' => 3,
        //     'name' => 'ADVC-Teste',
        // ]);
        // $tenant->domains()->create(['id' => 4, 'domain' => 'advcteste.advocaciateste', 'name' => 'ADVC-Teste']);
    }
}
