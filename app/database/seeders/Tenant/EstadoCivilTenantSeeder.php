<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\EstadoCivilTenant;
use Illuminate\Database\Seeder;

class EstadoCivilTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $insert = [
            [
                "id" => "9dbdf284-d3e1-48db-b459-d52f42dc3041",
                "nome" => "Casado (a)",
            ],
            [
                "id" => "9dbdf292-0b97-4330-9562-91df27b24924",
                "nome" => "Divorciado (a)",
            ],
            [
                "id" => "9dbdf2aa-79f1-4635-b6f4-1fba363ba729",
                "nome" => "Separado judicialmente",
            ],
            [
                "id" => "9dbdf276-f665-49ca-9cd8-0c9060477810",
                "nome" => "Solteiro (a)",
            ],
            [
                "id" => "9dbdf29d-969d-4ad4-9e4c-77604b539cb1",
                "nome" => "Viúvo (a)",
            ]
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            EstadoCivilTenant::create($data);
        }
    }
}
