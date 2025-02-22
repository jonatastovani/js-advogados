<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\AreaJuridicaTenant;
use Illuminate\Database\Seeder;

class AreaJuridicaTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'nome' => 'Cível',
                'tenant_id' => 'jsadvogados',
            ],
            [
                'nome' => 'Família',
                'tenant_id' => 'jsadvogados',
            ],
            [
                'nome' => 'Previdenciário',
                'tenant_id' => 'jsadvogados',
            ],
            [
                'nome' => 'Empresarial / Tributário',
                'tenant_id' => 'jsadvogados',
            ],
            [
                'nome' => 'Sucessões',
                'tenant_id' => 'jsadvogados',
            ],
            [
                'nome' => 'Criminal',
                'tenant_id' => 'jsadvogados',
            ],
            [
                'nome' => 'Trabalhista',
                'tenant_id' => 'jsadvogados',
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            AreaJuridicaTenant::create($data);
        }
    }
}
