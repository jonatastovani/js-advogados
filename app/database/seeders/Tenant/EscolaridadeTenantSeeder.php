<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\EscolaridadeTenant;
use Illuminate\Database\Seeder;

class EscolaridadeTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $insert = [
            [
                "id" => "9dbdf3c8-ecb6-4fbb-a824-c740e9d31f36",
                "nome" => "Analfabeto",
            ],
            [
                "id" => "9dbdf3e4-455b-40c1-b2b0-d06bb1b2bc3f",
                "nome" => "Fundamental - Completo",
            ],
            [
                "id" => "9dbdf3dc-7b00-49b3-a956-da15f1763479",
                "nome" => "Fundamental - Incompleto",
            ],
            [
                "id" => "9dbdf3fb-ab0e-435e-b1a9-d0b7012b6d36",
                "nome" => "Médio - Completo",
            ],
            [
                "id" => "9dbdf3f3-5d24-4559-9a2b-aa432ff340f9",
                "nome" => "Médio - Incompleto",
            ],
            [
                "id" => "9dbdf41c-0730-4e1c-862b-3ac3de2eab7e",
                "nome" => "Superior - Completo",
            ],
            [
                "id" => "9dbdf413-bac4-4cbc-92bc-06dc0d441d0d",
                "nome" => "Superior - Incompleto",
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            EscolaridadeTenant::create($data);
        }
    }
}
