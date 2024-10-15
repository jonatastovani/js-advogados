<?php

namespace Database\Seeders\Referencias;

use App\Helpers\UUIDsHelpers;
use App\Models\Referencias\ContaStatusTipo;
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
