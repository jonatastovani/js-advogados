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
            [
                'id' => "9dbde119-ebf9-4541-8722-6e18078d1321",
                'nome' => 'Título Eleitoral',
                'documento_tipo_id' => DocumentoTipoEnum::TITULO_ELEITORAL,
                'configuracao' => [
                    'quantidade_permitida' => 1,
                ],
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        foreach ($insert as $data) {
            $resource = $this->model::find($data['id']);
            if (!$resource) {
                $resource = new $this->model;
                $data['created_user_id'] = $adminTenantUserId;
                $resource->create($data);
            } else {
                $data['updated_user_id'] = $adminTenantUserId;
                $resource->update($data);
            }
        }
    }
}
