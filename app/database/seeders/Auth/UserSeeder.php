<?php

namespace Database\Seeders\Auth;

use App\Helpers\UUIDsHelpers;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
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
                'id' => $adminId,
                'name' => 'Administrador',
                'email' => 'byteforgesuporte@gmail.com',
                'password' => 'admin987123',
                'tenant_id' => 'admin',
                'created_user_id' => $adminTenantUserId
            ],
        ];

        foreach ($insert as $data) {
            User::create($data);
        }
    }
}
