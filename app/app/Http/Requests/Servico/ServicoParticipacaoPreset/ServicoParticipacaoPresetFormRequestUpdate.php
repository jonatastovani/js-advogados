<?php

namespace App\Http\Requests\Servico\ServicoParticipacaoPreset;

class ServicoParticipacaoPresetFormRequestUpdate extends ServicoParticipacaoPresetFormRequestBase
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
        $rules = parent::rules();
        $rules =  array_merge($rules, [
            'participantes.*.id' => 'nullable|uuid',
            'participantes.*.integrantes.*.id' => 'nullable|uuid',
        ]);
        return $rules;
    }
}