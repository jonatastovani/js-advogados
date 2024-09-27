<?php

namespace Database\Seeders;

use App\Helpers\EnumPermissionConfigsHelper;
use App\Helpers\EnumPermissionGroupsHelper;
use App\Helpers\EnumPermissionsHelper;
use App\Helpers\UUIDsHelpers;
use App\Models\Auth\PermissionConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $insert = [
            [
                'permissao_id' => EnumPermissionsHelper::DESENVOLVEDOR,
                'gerencia_perm_bln' => true,
                'grupo_id' => EnumPermissionGroupsHelper::ADMINISTRADOR,
            ],
            [
                'permissao_id' => EnumPermissionsHelper::ADMINISTRADOR,
                'gerencia_perm_bln' => true,
                'grupo_id' => EnumPermissionGroupsHelper::ADMINISTRADOR,
            ],
            [
                'permissao_id' => EnumPermissionsHelper::SUPORTE_SISTEMA,
                'gerencia_perm_bln' => true,
                'grupo_id' => EnumPermissionGroupsHelper::SUPORTE,
            ],
            [
                'permissao_id' => EnumPermissionsHelper::ADMIN_ADVOCACIA,
                'gerencia_perm_bln' => true,
                'grupo_id' => EnumPermissionGroupsHelper::ADMINISTRACAO_MODULO_ADVOCACIA,
                'permissao_pai_id' => EnumPermissionsHelper::ADMINISTRADOR,
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            PermissionConfig::create($data);
        }
    }
}
