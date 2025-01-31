<?php

namespace Database\Seeders\Tenant;

use App\Enums\ParticipacaoTipoTenantConfiguracaoTipoEnum;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class ParticipacaoTipoTenantObrigatorioSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\ParticipacaoTipoTenant();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $dataList = [
            [
                "nome" => "Empresa",
                "descricao" => "Participação da empresa em lançamentos gerais",
                "bloqueado_para_usuario_comum" => true,
                "tipo" => ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_GERAL->value,
                "tag" => [
                    "participacao_empresa_movimentacao"
                ],
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->upsertData($dataList);
    }
}
