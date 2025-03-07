<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\Tenant;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class DomainAdminSeeder extends Seeder
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
                'id' => 1,
                'domain' => 'admin.byteforge.com.br',
                'name' => 'Admin',
                'created_user_id' => $idAdmin,
            ]
        ];

        // Chama o método genérico para inserção/atualização
        $this->setTenantId('admin')->setAtualizaIdIncrementalBln(true)->upsertData($dataList);
    }
}
