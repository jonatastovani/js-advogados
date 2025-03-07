<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\Tenant;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class DomainEscritorioSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Auth\Domain();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idAdmin = UUIDsHelpers::getAdminTenantUser();

        $dataList = [
            [
                'id' => 2,
                'domain' => 'jsadvogados.byteforge.com.br',
                'name' => 'Matriz',
                'created_user_id' => $idAdmin,
            ],
            [
                'id' => 3,
                'domain' => 'jsadvogadosprev.byteforge.com.br',
                'name' => 'Prev',
                'created_user_id' => $idAdmin,
            ],
            [
                'id' => 4,
                'domain' => 'jsadvogadostupi.byteforge.com.br',
                'name' => 'Unidade 2',
                'created_user_id' => $idAdmin,
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
