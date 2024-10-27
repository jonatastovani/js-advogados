<?php

namespace App\Http\Requests\Servico\ServicoParticipacaoPreset;

use App\Enums\ParticipacaoRegistroTipoEnum;
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
            'participantes.*.referencia_id' => 'nullable|uuid',
            'participantes.*.participacao_tipo_id' => 'required|uuid',
            'participantes.*.valor_tipo' => 'required|string|in:porcentagem,valor_fixo',
            'participantes.*.valor' => 'required|numeric|min:0.01',
            'participantes.*.observacao' => 'nullable|string',
            'participantes.*.integrantes' => 'nullable|array',
            'participantes.*.integrantes.*.participacao_registro_tipo_id' => 'required|integer|in:1',
            'participantes.*.integrantes.*.referencia_id' => 'required|uuid',
        ];

        // Adicionar regras condicionais
        $this->addConditionalRules($rules);

        return $rules;
    }

    protected function addConditionalRules(array &$rules)
    {
        // Condicional para participacao_registro_tipo_id = 1
        $this->merge([
            'participantes.*.referencia_id' => 'sometimes|required_if:participantes.*.participacao_registro_tipo_id,1|uuid',
        ]);

        // Condicional para participacao_registro_tipo_id = 2
        $this->merge([
            'participantes.*.nome_grupo' => 'sometimes|required_if:participantes.*.participacao_registro_tipo_id,2|string',
            'participantes.*.integrantes' => 'sometimes|required_if:participantes.*.participacao_registro_tipo_id,2|array|min:1',
        ]);
    }

    // protected function customAttributeNames(): array
    // {
    //     return [
    //         'nome' => 'título',
    //         'descricao' => 'descrição',
    //         'ativo_bln' => 'ativo'
    //     ];
    // }
}
