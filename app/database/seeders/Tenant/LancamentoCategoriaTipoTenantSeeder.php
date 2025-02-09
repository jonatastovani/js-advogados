<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use Illuminate\Database\Seeder;

class LancamentoCategoriaTipoTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                "id" => "9da37988-52c8-4c13-b033-838a3f5c8cf8",
                "tenant_id" => "jsadvogados",
                "nome" => "Despesas Fixas",
            ],
            [
                "id" => "9da37996-201b-4fa1-92a5-dd8d92736291",
                "tenant_id" => "jsadvogados",
                "nome" => "Despesas Variáveis",
            ],  
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            LancamentoCategoriaTipoTenant::create($data);
        }
    }
}
