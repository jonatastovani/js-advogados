<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\Tenant;
use Illuminate\Database\Seeder;

class TenantDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idAdmin = UUIDsHelpers::getAdminTenantUser();

        $tenant = Tenant::create([
            'id' => 'admin',
            'tenant_type_id' => 1,
            'name' => 'Administrador',
            'created_user_id' => $idAdmin,
            'sigla' => 'ADM'
        ]);
        $tenant->domains()->create([
            'id' => 1,
            'domain' => 'admin.byteforge.com.br',
            'name' => 'Admin',
            'created_user_id' => $idAdmin,
        ]);

        $tenant = Tenant::create([
            'id' => 'jsadvogados',
            'tenant_type_id' => 4,
            'name' => 'JS-Advogados',
            'created_user_id' => $idAdmin,
            'sigla' => 'JS-Adv'
        ]);

        $tenant->domains()->create([
            'id' => 2,
            'domain' => 'jsadvogadosmc.byteforge.com.br',
            'name' => 'Monte Castelo',
            'created_user_id' => $idAdmin,
        ]);
        $tenant->domains()->create([
            'id' => 3,
            'domain' => 'jsadvogadosprev.byteforge.com.br',
            'name' => 'Prev',
            'created_user_id' => $idAdmin,
        ]);

        // $tenant = Tenant::create([
        //     'id' => 'advocaciateste',
        //     'tenant_type_id' => 3,
        //     'name' => 'ADVC-Teste',
        // ]);
        // $tenant->domains()->create(['id' => 4, 'domain' => 'advcteste.advocaciateste', 'name' => 'ADVC-Teste']);
    }
}
