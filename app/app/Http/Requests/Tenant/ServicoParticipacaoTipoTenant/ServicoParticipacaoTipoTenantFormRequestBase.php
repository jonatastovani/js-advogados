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
            'configuracao' => 'required|array',
            'configuracao.bloqueado_para_usuario_comum' => 'nullable|boolean',
            'configuracao.tipo' => 'required|string',
            'configuracao.tag' => 'nullable|array',
        ];
    }
}
