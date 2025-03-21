<?php

namespace Database\Seeders\Tenant;

use App\Enums\DocumentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class DocumentoTipoTenantUpdateSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Tenant\DocumentoTipoTenant();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [
            [
                'id' => "9dbde118-3e17-4a87-b5c5-8e595929a2ec",
                'nome' => 'CPF',
                'documento_tipo_id' => DocumentoTipoEnum::CPF->value,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dbde119-e570-4e05-a67e-521a1d52c5f6",
                'nome' => 'CNPJ',
                'documento_tipo_id' => DocumentoTipoEnum::CNPJ->value,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dbde119-e888-4646-a6e2-c38bed7c3193",
                'nome' => 'RG',
                'documento_tipo_id' => DocumentoTipoEnum::RG->value,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dd02ad9-e3e6-4d7a-ace5-d42febf377aa",
                'nome' => 'Passaporte',
                'documento_tipo_id' => DocumentoTipoEnum::PASSAPORTE->value,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dd02ad9-f2f5-4b9d-a256-1ff011ac1c61",
                'nome' => 'Inscrição Estadual',
                'documento_tipo_id' => DocumentoTipoEnum::INSCRICAO_ESTADUAL->value,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dd02ad9-f592-41b8-ad47-03740f08f34f",
                'nome' => 'Inscrição Municipal',
                'documento_tipo_id' => DocumentoTipoEnum::INSCRICAO_MUNICIPAL->value,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
            [
                'id' => "9dd02ad9-f9bb-45bd-aad0-2bbaba2a4dcb",
                'nome' => 'CNAE',
                'documento_tipo_id' => DocumentoTipoEnum::CNAE->value,
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
            $resource = isset($data['id']) ? $this->model::find($data['id']) : null;
            if (!$resource) {
                $resource = new $this->model;
                $data['created_user_id'] = $adminTenantUserId;
                $data['tenant_id'] = 'jsadvogados';
                $resource->create($data);
            } else {
                $data['updated_user_id'] = $adminTenantUserId;
                $resource->update($data);
            }
        }
    }
}
