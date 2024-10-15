<?php

namespace Database\Seeders;

use App\Helpers\EnumPermissionConfigsHelper;
use App\Enums\PermissionGroupsEnum;
use App\Enums\PermissionsEnum;
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
                'permissao_id' => PermissionsEnum::DESENVOLVEDOR,
                'gerencia_perm_bln' => true,
                'grupo_id' => PermissionGroupsEnum::ADMINISTRADOR,
            ],
            [
                'permissao_id' => PermissionsEnum::ADMINISTRADOR,
                'gerencia_perm_bln' => true,
                'grupo_id' => PermissionGroupsEnum::ADMINISTRADOR,
            ],
            [
                'permissao_id' => PermissionsEnum::SUPORTE_SISTEMA,
                'gerencia_perm_bln' => true,
                'grupo_id' => PermissionGroupsEnum::SUPORTE,
            ],
            [
                'permissao_id' => PermissionsEnum::ADMIN_ADVOCACIA,
                'gerencia_perm_bln' => true,
                'grupo_id' => PermissionGroupsEnum::ADMINISTRACAO_MODULO_ADVOCACIA,
                'permissao_pai_id' => PermissionsEnum::ADMINISTRADOR,
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            PermissionConfig::create($data);
        }
    }
}
