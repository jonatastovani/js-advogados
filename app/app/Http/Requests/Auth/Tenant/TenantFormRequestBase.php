<?php

namespace App\Http\Requests\Auth\Tenant;

use App\Http\Requests\BaseFormRequest;

class TenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            'id' => 'required|string',
            'name' => 'required|string|max:30',
            'tenant_type_id' => 'required|integer',
            // Pode mandar o sigla desta forma porque a trait VirtualColumn faz o encode e o decode dentro do json
            'sigla' => 'required|string|max:10',
            'domains' => 'required|array|min:1',
            'domains.*.id' => 'required|integer',
            'domains.*.domain' => 'required|string|max:100',
            'domains.*.name' => 'required|string|max:30',
            'lancamento_liquidado_migracao_sistema_bln' => 'nullable|boolean',
            'cancelar_liquidado_migracao_sistema_automatico_bln' => 'nullable|boolean',
            'order_by_servicos_lancamentos_listagem_array' => 'nullable|array',
            'order_by_servicos_lancamentos_edicao_array' => 'nullable|array',
        ];

        return $rules;
    }
}
