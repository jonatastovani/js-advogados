<?php

namespace Database\Seeders\Tenant;

use App\Enums\ParticipacaoTipoTenantConfiguracaoTipoEnum;
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
                "descricao" => "Participação da empresa em lançamentos gerais",
                "configuracao" => [
                    "bloqueado_para_usuario_comum" => true,
                    "tipo" => ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_GERAL->value,
                    "tag" => [
                        "participacao_empresa_movimentacao"
                    ],
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
