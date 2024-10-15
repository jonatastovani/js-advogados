<?php

namespace Database\Seeders\Financeiro;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Financeiro\PagamentoTipoTenant;
use Illuminate\Database\Seeder;

class PagamentoTipoTenantSeeder extends Seeder
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
                'nome' => 'Parcela Única',
                'pagamento_tipo_id' => PagamentoTipoEnum::PAGAMENTO_UNICO,
                'configuracao' => [],
            ],
            [
                'nome' => 'Entrada + Parcelado',
                'pagamento_tipo_id' => PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO,
                'configuracao' => [],
            ],
            [
                'nome' => 'Parcelado',
                'pagamento_tipo_id' => PagamentoTipoEnum::PARCELADO,
                'configuracao' => [],
            ],
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();

        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            $data['tenant_id'] = 'jsadvogados';
            PagamentoTipoTenant::create($data);
        }
    }
}
