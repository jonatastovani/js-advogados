<?php

namespace Database\Seeders;

use App\Helpers\EnumPermissionModulesHelper;
use App\Helpers\UUIDsHelpers;
use App\Models\Auth\PermissionModule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [];
        foreach (EnumPermissionModulesHelper::cases() as $enumValue) {
            $insert[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            PermissionModule::create($data);
        }

        $maxId = PermissionModule::max('id');  // Obtém o maior ID atual na tabela
        if ($maxId) {
            // Substitua "tenant_types_id_seq" pelo nome correto da sequência para sua tabela e coluna
            $sequenceName = PermissionModule::getTableName() . '_id_seq';  // Nome da sequência associada à coluna ID da tabela
            DB::statement('SELECT setval(\'' . $sequenceName . '\', ' . ($maxId + 1) . ', false)');
        }
    }
}
