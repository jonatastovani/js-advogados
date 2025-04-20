<?php

namespace Database\Seeders\Tenant;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class PagamentoTipoTenantSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\PagamentoTipoTenant();
    }

    public function run(): void
    {
        $dataList = [
            // [
            //     'nome' => 'Parcela Única',
            //     'pagamento_tipo_id' => PagamentoTipoEnum::PAGAMENTO_UNICO,
            // ],
            // [
            //     'nome' => 'Entrada + Parcelado',
            //     'pagamento_tipo_id' => PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO,
            // ],
            // [
            //     'nome' => 'Parcelado',
            //     'pagamento_tipo_id' => PagamentoTipoEnum::PARCELADO,
            // ],
            // [
            //     'nome' => 'Recorrente',
            //     'pagamento_tipo_id' => PagamentoTipoEnum::RECORRENTE,
            // ],
            // [
            //     'nome' => 'Condicionado',
            //     'pagamento_tipo_id' => PagamentoTipoEnum::CONDICIONADO,
            // ],
            [
                'nome' => 'Pagamento Livre',
                'pagamento_tipo_id' => PagamentoTipoEnum::LIVRE_INCREMENTAL,
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->upsertData($dataList);
    }
}
