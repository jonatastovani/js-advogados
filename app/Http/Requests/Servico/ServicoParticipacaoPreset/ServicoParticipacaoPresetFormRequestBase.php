<?php

namespace App\Http\Requests\Servico\ServicoParticipacaoPreset;

use App\Http\Requests\BaseFormRequest;

class ServicoParticipacaoPresetFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'ativo_bln' => 'nullable|boolean',
            'participantes' => 'required|array|min:1',
            'participantes.*.participacao_registro_tipo_id' => 'required|integer|in:1,2',
            'participantes.*.nome_grupo' => 'required_if:participantes.*.participacao_registro_tipo_id,2|string',
            'participantes.*.referencia_id' => 'required_if:participantes.*.participacao_registro_tipo_id,1|uuid',
            'participantes.*.participacao_tipo_id' => 'required|uuid',
            'participantes.*.valor_tipo' => 'required|string|in:porcentagem,valor_fixo',
            'participantes.*.valor' => 'required|numeric|min:0.01',
            'participantes.*.observacao' => 'nullable|string',
            'participantes.*.integrantes' => 'required_if:participantes.*.participacao_registro_tipo_id,2|array|min:1',
            'participantes.*.integrantes.*.participacao_registro_tipo_id' => 'required|integer|in:1',
            'participantes.*.integrantes.*.referencia_id' => 'required|uuid',
        ];

        return $rules;
    }
}
