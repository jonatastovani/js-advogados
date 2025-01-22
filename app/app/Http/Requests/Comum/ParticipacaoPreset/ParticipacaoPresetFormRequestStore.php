<?php

namespace App\Http\Requests\Comum\ParticipacaoPreset;

class ParticipacaoPresetFormRequestStore extends ParticipacaoPresetFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
