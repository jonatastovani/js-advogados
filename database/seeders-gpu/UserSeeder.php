<?php

namespace Database\Seeders;

use App\Helpers\UUIDsHelpers;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = UUIDsHelpers::getAdmin();
        $gpuOnlineApi = UUIDsHelpers::getGpuOnlineApi();

        $insert = [
            [
                'id' => $adminId,
                'nome' => 'Administrador',
                'username' => 'admin',
                // 'password' => '!@#$56Qw',
                'password' => 'admin123',
            ],
            [
                'id' => $gpuOnlineApi,
                'nome' => 'GPU Online API - Legacy',
                'username' => 'gpuonlineapi',
                'password' => '!@#$56Qw',
            ],
        ];

        foreach ($insert as $data) {
            User::create($data);
        }

        // User::create([
        //     'nome' => 'Administrador',
        //     'username' => 'admin',
        //     'password' => '!@#$56Qw',
        //     // 'descricao' => 'Acesso de administrador do sistema.',
        //     // 'tenant_id' => 'admin',
        //     // 'email' => 'admin@gpu.sap.sp.br',
        //     // 'origin_ip_access' => 'all',
        // ]);
        // User::create([
        //     'nome' => 'GPU Online API - Legacy',
        //     'username' => 'gpuonlineapi',
        //     'password' => '!@#$56Qw',
        //     // 'descricao' => 'Acesso do Front do GPU.',
        //     // 'tenant_id' => 'gpu-online-api',
        //     // 'email' => 'gpuonline@gpu.sap.sp.br',
        //     // 'origin_ip_access' => env('APP_ENV') === 'production' ?  env('ALLOWED_IP_FRONT_PROD') : env('ALLOWED_IP_FRONT_DEV'),
        // ]);

        // // $adminId = UUIDsHelpers::getAdmin();
        // // User::create([
        // //     'id' => $adminId,
        // //     'name' => 'Admin',
        // //     'email' => 'admin@ravel',
        // //     'password' => bcrypt('password'),
        // //     'tenant_id' => 'admin'
        // // ]);
    }
}
