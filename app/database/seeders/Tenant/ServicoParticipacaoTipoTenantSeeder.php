<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\ServicoParticipacaoTipoTenant;
use Illuminate\Database\Seeder;

class ServicoParticipacaoTipoTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $insert = [
            [
                "id"=> "9d714390-0789-44c9-a7db-886b39c0610f",
                "nome"=> "Atuação Principal",
            ],
            [
                "id"=> "9d7143a8-3391-4e4a-99c5-d31a56ed6a78",
                "nome"=> "Captação",
            ],
            [
                "id"=> "9d7143a1-990f-47f8-987e-2332d02cb360",
                "nome"=> "Escritório",
            ],
            [
                "id"=> "9d714394-befe-4f2e-bba2-58b0c351c6d0",
                "nome"=> "Cashback",
            ]
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            ServicoParticipacaoTipoTenant::create($data);
        }
    }
}
