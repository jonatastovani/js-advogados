<?php

namespace App\Http\Requests\Tenant\ServicoParticipacaoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class ServicoParticipacaoTipoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'configuracao_tipo' => 'required|string',
        ];
    }
}
