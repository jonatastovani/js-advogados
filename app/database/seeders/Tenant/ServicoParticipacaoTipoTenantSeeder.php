<?php

namespace Database\Seeders\Tenant;

use App\Enums\ParticipacaoTipoTenantConfiguracaoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class ServicoParticipacaoTipoTenantSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\ServicoParticipacaoTipoTenant();
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
                "id" => "9d714390-0789-44c9-a7db-886b39c0610f",
                "nome" => "Atuação Principal",
                "tipo" => ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_SERVICO->value,
            ],
            [
                "id" => "9d7143a8-3391-4e4a-99c5-d31a56ed6a78",
                "nome" => "Captação",
                "tipo" => ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_SERVICO->value,
            ],
            [
                "id" => "9d7143a1-990f-47f8-987e-2332d02cb360",
                "nome" => "Escritório",
                "tipo" => ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_SERVICO->value,
            ],
            [
                "id" => "9d714394-befe-4f2e-bba2-58b0c351c6d0",
                "nome" => "Cashback",
                "tipo" => ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_SERVICO->value,
            ]
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId('jsadvogados')->upsertData($dataList);
    }
}
