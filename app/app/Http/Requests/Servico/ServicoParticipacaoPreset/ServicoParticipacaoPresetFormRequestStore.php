<?php

namespace App\Http\Requests\Servico\ServicoParticipacaoPreset;

class ServicoParticipacaoPresetFormRequestStore extends ServicoParticipacaoPresetFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
