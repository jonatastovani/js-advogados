<?php

namespace Database\Seeders\Referencias;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Referencias\PagamentoTipo;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagamentoTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;

    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\PagamentoTipo();
    }

    public function run(): void
    {
        $insert = [];
        foreach (PagamentoTipoEnum::cases() as $enumValue) {
            $insert[] = $enumValue->detalhes();  // Puxa os detalhes de cada enum
        }

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $this->model::create($data);
        }

        $this->atualizaIdIncrementalNumerico();
    }
}
