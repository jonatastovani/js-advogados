<?php

namespace App\Http\Requests\GPU\Inteligencia;

class InformacaoSubjetivaRequestStore extends InformacaoSubjetivaRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
