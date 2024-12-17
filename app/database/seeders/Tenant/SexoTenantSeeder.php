<?php

namespace Database\Seeders\Tenant;

use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\SexoTenant;
use Illuminate\Database\Seeder;

class SexoTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $insert = [
            [
                "id"=> "9dbdf463-69ef-4266-9c3f-91685ef6a852",
                "nome"=> "Feminino",
            ],
            [
                "id"=> "9dbdf44f-e6c0-437b-a093-bdbaf7dd7147",
                "nome"=> "Masculino",
            ]
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            SexoTenant::create($data);
        }
    }
}
