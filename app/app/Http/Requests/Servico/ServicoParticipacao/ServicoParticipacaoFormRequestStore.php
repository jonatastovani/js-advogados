<?php

namespace App\Http\Requests\Servico\ServicoParticipacao;

class ServicoParticipacaoFormRequestStore extends ServicoParticipacaoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
