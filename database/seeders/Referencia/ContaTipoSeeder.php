<?php

namespace Database\Seeders\Referencia;

use App\Helpers\UUIDsHelpers;
use App\Models\Referencias\ContaTipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'id' => '1',
                'nome' => 'Depósito',
            ],
            [
                'id' => '2',
                'nome' => 'Salário',
            ],
            [
                'id' => '3',
                'nome' => 'Pagamento',
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            ContaTipo::create($data);
        }
    }
}
