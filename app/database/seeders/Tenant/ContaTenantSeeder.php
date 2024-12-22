<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\ContaTenant;
use Illuminate\Database\Seeder;

class ContaTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $insert = [
            [
                'nome' => 'Conta Recebimento',
                'descricao' => 'Recebimento geral',
                'conta_subtipo_id' => 4,
                'banco' => 'Banco do Brasil',
                'conta_status_id' => 1,
            ],
            [
                'nome' => 'Conta Principal',
                'descricao' => 'Recebimento geral',
                'conta_subtipo_id' => 1,
                'banco' => 'Banco Itaú',
                'conta_status_id' => 1,
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            $data['domain_id'] = 2;
            ContaTenant::create($data);
        }
    }
}
