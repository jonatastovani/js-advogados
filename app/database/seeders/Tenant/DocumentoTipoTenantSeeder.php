<?php

namespace Database\Seeders\Tenant;

use App\Enums\DocumentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\DocumentoTipoTenant;
use Illuminate\Database\Seeder;

class DocumentoTipoTenantSeeder extends Seeder
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
                'id' => "9dbde118-3e17-4a87-b5c5-8e595929a2ec",
                'nome' => 'CPF',
                'documento_tipo_id' => DocumentoTipoEnum::CPF,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dbde119-e570-4e05-a67e-521a1d52c5f6",
                'nome' => 'CNPJ',
                'documento_tipo_id' => DocumentoTipoEnum::CNPJ,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dbde119-e888-4646-a6e2-c38bed7c3193",
                'nome' => 'RG',
                'documento_tipo_id' => DocumentoTipoEnum::RG,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            // [
            //     'id' => "9dbde119-ebf9-4541-8722-6e18078d1321",
            //     'nome' => 'Título Eleitoral',
            //     'documento_tipo_id' => DocumentoTipoEnum::TITULO_ELEITORAL,
            //     'configuracao' => [
            //         'quantidade_permitida' => 1,
            //     ],
            // ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            DocumentoTipoTenant::create($data);
        }
    }
}
