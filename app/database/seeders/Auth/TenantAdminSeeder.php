<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\Tenant;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class TenantAdminSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Auth\Tenant();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idAdmin = UUIDsHelpers::getAdminTenantUser();

        $dataList = [
            [
                'id' => 'admin',
                'tenant_type_id' => 1,
                'name' => 'Administrador',
                'created_user_id' => $idAdmin,
                'sigla' => 'ADM'
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->upsertData($dataList);
    }
}
