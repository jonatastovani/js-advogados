<?php

namespace Database\Seeders\Tenant;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\UUIDsHelpers;
use App\Models\Tenant\PagamentoTipoTenant;
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
            ],
            [
                'nome' => 'Entrada + Parcelado',
                'pagamento_tipo_id' => PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO,
            ],
            [
                'nome' => 'Parcelado',
                'pagamento_tipo_id' => PagamentoTipoEnum::PARCELADO,
            ],
            [
                'nome' => 'Recorrente',
                'pagamento_tipo_id' => PagamentoTipoEnum::RECORRENTE,
            ],
            [
                'nome' => 'Condicionado',
                'pagamento_tipo_id' => PagamentoTipoEnum::CONDICIONADO,
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
