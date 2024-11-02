<?php

namespace Database\Seeders;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\UserTenantDomain;
use Illuminate\Database\Seeder;

class UserTenantDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = UUIDsHelpers::getAdmin();
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        $insert = [
            [
                'id' => $adminTenantUserId,
                'user_id' => $adminId,
                'tenant_id' => 'admin',
                'domain_id' => 1,
            ],
            [
                'user_id' => $adminId,
                'tenant_id' => 'jsadvogados',
                'domain_id' => 2,
            ],
        ];

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            UserTenantDomain::create($data);
        }
    }
}
