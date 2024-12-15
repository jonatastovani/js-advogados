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
                'nome' => 'CPF',
                'documento_tipo_id' => DocumentoTipoEnum::CPF,
                'configuracao' => [],
            ],
            [
                'nome' => 'CNPJ',
                'documento_tipo_id' => DocumentoTipoEnum::CNPJ,
                'configuracao' => [],
            ],
            [
                'nome' => 'RG',
                'documento_tipo_id' => DocumentoTipoEnum::RG,
                'configuracao' => [],
            ],
            [
                'nome' => 'Título Eleitoral',
                'documento_tipo_id' => DocumentoTipoEnum::TITULO_ELEITORAL,
                'configuracao' => [],
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            DocumentoTipoTenant::create($data);
        }
    }
}
