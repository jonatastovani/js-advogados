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
                'permissao_id' => PermissionsEnum::SUPORTE_GPU,
                'gerencia_perm_bln' => true,
                'grupo_id' => PermissionGroupsEnum::SUPORTE,
            ],
            [
                'permissao_id' => PermissionsEnum::ADMIN_INTELIGENCIA,
                'gerencia_perm_bln' => true,
                'grupo_id' => PermissionGroupsEnum::ADMINISTRACAO_MODULO_INTELIGENCIA,
                'permissao_pai_id' => PermissionsEnum::ADMINISTRADOR,
            ],
            [
                'permissao_id' => PermissionsEnum::VISUALIZAR_INFORMACOES_SUBJETIVAS,
                'grupo_id' => PermissionGroupsEnum::INFORMACAO_SUBJETIVA,
                'permissao_pai_id' => PermissionsEnum::ADMIN_INTELIGENCIA,
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            PermissionConfig::create($data);
        }
    }
}
