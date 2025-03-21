<?php

namespace Database\Seeders\Tenant;

use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class FormaPagamentoTenantSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\FormaPagamentoTenant();
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
                "nome" => "Pix JS PJ",
                'conta_id' => '9e18b6e1-14d6-43ed-b8ac-89a5ba725570',
            ],
            [
                "nome" => "Boleto JS PJ",
                'conta_id' => '9e18b6e1-14d6-43ed-b8ac-89a5ba725570',
            ],
            [
                "nome" => "Cheque",
                'conta_id' => '9e18b6e1-187c-4795-9e52-5e491fc00985',
            ],
            [
                "nome" => "Dinheiro",
                'conta_id' => '9e18b6e1-187c-4795-9e52-5e491fc00985',
            ],
            [
                "nome" => "Dep. Jud. Conta PF. JS",
                'conta_id' => '9e18b6e1-187c-4795-9e52-5e491fc95485',
            ],
            [
                "nome" => "Pix JS PJ Itaú",
                'conta_id' => '9e18b6e1-187c-4795-9e52-5e491fc95485',
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->setDomainId(2)->upsertData($dataList);
    }
}
