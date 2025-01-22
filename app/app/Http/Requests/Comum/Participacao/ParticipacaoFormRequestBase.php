<?php

namespace App\Http\Requests\Comum\Participacao;

use App\Http\Requests\BaseFormRequest;

class ParticipacaoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'participantes.*.id' => 'nullable|uuid',
            'participantes' => 'required|array|min:1',
            'participantes.*.participacao_registro_tipo_id' => 'required|integer|in:1,2',
            'participantes.*.nome_grupo' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,2|string',
            'participantes.*.referencia_id' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,1|uuid',
            'participantes.*.participacao_tipo_id' => 'required|uuid',
            'participantes.*.valor_tipo' => 'required|string|in:porcentagem,valor_fixo',
            'participantes.*.valor' => 'required|numeric|min:0.01',
            'participantes.*.observacao' => 'nullable|string',
            'participantes.*.integrantes.*.id' => 'nullable|uuid',
            'participantes.*.integrantes' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,2|array|min:1',
            'participantes.*.integrantes.*.participacao_registro_tipo_id' => 'required|integer|in:1',
            'participantes.*.integrantes.*.referencia_id' => 'required|uuid',
        ];

        return $rules;
    }
}
