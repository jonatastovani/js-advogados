<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\ServicoParticipacaoTipoTenant;
use Illuminate\Database\Seeder;

class ServicoParticipacaoTipoTenantObrigatorioSeeder extends Seeder
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
                "nome" => "Empresa",
                "configuracao" => [
                    "oculto_para_usuario" => true,
                    "tipo" => "participacao_empresa_movimentacao",
                ]
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            ServicoParticipacaoTipoTenant::create($data);
        }
    }
}
