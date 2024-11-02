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
            'nome' => 'Administrador',
        ]);
        $tenant->domains()->create(['id' => 1, 'domain' => 'admin.byteforge.com.br']);

        $tenant = Tenant::create([
            'id' => 'jsadvogados',
            'tenant_type_id' => 3,
            'nome' => 'JS-Advogados',
        ]);
        $tenant->domains()->create(['id' => 2, 'domain' => 'jsadvogadosmc.byteforge.com.br']);
        $tenant->domains()->create(['id' => 3, 'domain' => 'jsadvogadosprev.byteforge.com.br']);

        // $tenant = Tenant::create([
        //     'id' => 'advocaciateste',
        //     'tenant_type_id' => 3,
        //     'nome' => 'ADVC-Teste',
        // ]);
        // $tenant->domains()->create(['id' => 4, 'domain' => 'advcteste.advocaciateste']);
    }
}
