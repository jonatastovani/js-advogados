<?php

namespace Database\Seeders\Financeiro;

use App\Helpers\UUIDsHelpers;
use App\Models\Financeiro\ContaStatusTipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContaStatusTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'nome' => 'Ativa',
            ],
            [
                'nome' => 'Inativa',
            ],
            [
                'nome' => 'Fechada',
            ],
            [
                'nome' => 'Bloqueada',
            ],
            [
                'nome' => 'Pendente',
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            ContaStatusTipo::create($data);
        }
    }
}
