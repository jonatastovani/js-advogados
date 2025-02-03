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
                "nome" => "Água - DAE",
            ],
            [
                "id" => "9da37996-201b-4fa1-92a5-dd8d92736291",
                "tenant_id" => "jsadvogados",
                "nome" => "Água - Galão",
            ],
            [
                "id" => "9da37940-5d79-42db-a5e3-2752a7911221",
                "tenant_id" => "jsadvogados",
                "nome" => "Aluguel",
            ],
            [
                "id" => "9da3797c-9e15-45a9-965a-b88658bfff7d",
                "tenant_id" => "jsadvogados",
                "nome" => "Energia",
            ],
            [
                "id" => "9da379a6-6943-40cc-96c0-493f854eb116",
                "tenant_id" => "jsadvogados",
                "nome" => "Internet",
            ],  
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            LancamentoCategoriaTipoTenant::create($data);
        }
    }
}
