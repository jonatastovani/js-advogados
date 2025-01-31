<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\ContaTenant;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class ContaTenantSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\ContaTenant();
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
                'id' => '9e18b6e1-14d6-43ed-b8ac-89a5ba725570',
                'nome' => 'Conta JS PJ',
                'descricao' => 'Recebimento geral',
                'conta_subtipo_id' => 4,
                'banco' => 'Banco do Brasil',
                'conta_status_id' => 1,
            ],
            [
                'id' => '9e18b6e1-187c-4795-9e52-5e491fc95485',
                'nome' => 'Conta Itaú PJ',
                'descricao' => 'Recebimento geral',
                'conta_subtipo_id' => 1,
                'banco' => 'Banco Itaú',
                'conta_status_id' => 1,
            ],
            [
                'id' => '9e18b6e1-187c-4795-9e52-5e491fc00985',
                'nome' => 'Cofre local',
                'descricao' => 'Cofre para recebimento em dinheiro e cheque',
                'conta_subtipo_id' => 5,
                'conta_status_id' => 1,
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->setDomainId(2)->upsertData($dataList);
    }
}
