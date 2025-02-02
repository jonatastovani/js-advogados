<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Auth\User();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = UUIDsHelpers::getAdmin();
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        $dataList = [
            [
                'id' => $adminId,
                'name' => 'Administrador',
                'email' => 'byteforgesuporte@gmail.com',
                'password' => 'admin987123',
                'tenant_id' => 'admin',
                'user_admin_master' => true,
                'created_user_id' => $adminTenantUserId
            ],
        ];

        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->upsertData($dataList);
    }
}
