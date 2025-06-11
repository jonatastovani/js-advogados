<?php

namespace App\Http\Requests\Auth\Tenant;

use App\Enums\TenantConfigExtrasEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class TenantFormRequestUpdateCliente extends TenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return Arr::only(parent::rules(), [
            'name',
            'sigla',
            'lancamento_liquidado_migracao_sistema_bln',
            'cancelar_liquidado_migracao_sistema_automatico_bln',
            TenantConfigExtrasEnum::ORDER_BY_SERVICOS_LANCAMENTOS_EDICAO_ARRAY->value,
            TenantConfigExtrasEnum::ORDER_BY_SERVICOS_LANCAMENTOS_LISTAGEM_ARRAY->value,
            'domains',
            'domains.*.id',
            'domains.*.name',
        ]);
    }
}
