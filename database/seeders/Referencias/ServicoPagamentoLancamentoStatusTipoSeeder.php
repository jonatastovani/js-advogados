<?php

namespace Database\Seeders\Referencias;

use App\Enums\ServicoPagamentoLancamentoStatusTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Traits\CommonsSeederMethodsTrait;
use Illuminate\Database\Seeder;

class ServicoPagamentoLancamentoStatusTipoSeeder extends Seeder
{
    use CommonsSeederMethodsTrait;
    
    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\Referencias\ServicoPagamentoLancamentoStatusTipo();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insert = [];
        foreach (ServicoPagamentoLancamentoStatusTipoEnum::cases() as $enumValue) {
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