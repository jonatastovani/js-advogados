<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\UserTenantDomain;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class UserTenantDomainSeeder extends Seeder
{

    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Auth\UserTenantDomain();
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
                'id' => $adminTenantUserId,
                'user_id' => $adminId,
                'tenant_id' => 'admin',
                'domain_id' => 1,
                'user_tenant_admin_master' => true,
            ],
            [
                'user_id' => $adminId,
                'tenant_id' => 'jsadvogados',
                'domain_id' => 2,
            ],
            [
                'user_id' => $adminId,
                'tenant_id' => 'jsadvogados',
                'domain_id' => 3,
            ],
        ];
        // Chama o método genérico para inserção/atualização
        $this->setDefaultTenantId()->upsertData($dataList);
    }
}
