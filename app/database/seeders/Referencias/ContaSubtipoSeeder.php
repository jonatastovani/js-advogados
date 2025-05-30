<?php

namespace Database\Seeders\Referencias;

use App\Helpers\UUIDsHelpers;
use App\Models\Referencias\ContaSubtipo;
use App\Models\Referencias\ContaTipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContaSubtipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'id' => 1,
                'nome' => 'Corrente',
                'conta_tipo_id' => 1,
            ],
            [
                'id' => 2,
                'nome' => 'Poupança',
                'conta_tipo_id' => 1,
            ],
            [
                'id' => 3,
                'nome' => 'Salário',
                'conta_tipo_id' => 2,
            ],
            [
                'id' => 4,
                'nome' => 'Pagamento',
                'conta_tipo_id' => 3,
            ],
            [
                'id' => 5,
                'nome' => 'Cofre local',
                'conta_tipo_id' => 4,
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            ContaSubtipo::create($data);
        }
    }
}
