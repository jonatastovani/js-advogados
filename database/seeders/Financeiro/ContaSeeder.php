<?php

namespace Database\Seeders\Financeiro;

use App\Helpers\UUIDsHelpers;
use App\Models\Financeiro\Conta;
use Illuminate\Database\Seeder;

class ContaSeeder extends Seeder
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
            Conta::create($data);
        }
    }
}
