<?php

namespace Database\Seeders\Referencias;

use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class MovimentacaoContaStatusTipoUpdateSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\MovimentacaoContaStatusTipo();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [];
        foreach (MovimentacaoContaStatusTipoEnum::cases() as $enumValue) {
            $insert[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

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

        $this->atualizaIdIncrementalNumerico();
    }
}
