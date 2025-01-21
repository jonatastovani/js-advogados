<?php

namespace App\Http\Requests\Tenant\ParticipacaoTipoTenant;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;

class PostConsultaFiltroFormRequestParticipacaoTipoTenant extends PostConsultaFiltroFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            'configuracao_tipo' => 'required|string',
        ]);
    }
}
