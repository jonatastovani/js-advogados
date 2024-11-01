<?php

namespace Database\Seeders;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\TenantUser;
use Illuminate\Database\Seeder;

class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = UUIDsHelpers::getAdmin();
        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        $gpuOnlineApi = UUIDsHelpers::getGpuOnlineApi();

        $insert = [
            [
                'id' => $adminTenantUserId,
                'user_id' => $adminId,
                'tenant_id' => 'admin',
            ],
            [
                'user_id' => $gpuOnlineApi,
                'tenant_id' => 'gpu-online-api',
            ],
            [
                'user_id' => $adminId,
                'tenant_id' => 'cdpamericana',
            ],
            [
                'user_id' => $adminId,
                'tenant_id' => 'inteligencia',
            ],
        ];

        foreach ($insert as $data) {
            TenantUser::create($data);
        }
    }
}
