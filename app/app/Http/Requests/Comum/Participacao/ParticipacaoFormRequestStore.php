<?php

namespace App\Http\Requests\Comum\Participacao;

class ParticipacaoFormRequestStore extends ParticipacaoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
