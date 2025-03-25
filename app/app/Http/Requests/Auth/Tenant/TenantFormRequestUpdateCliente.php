<?php

namespace App\Http\Requests\Auth\Tenant;

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
            'domains',
            'domains.*.id',
            'domains.*.name',
        ]);
    }
}
