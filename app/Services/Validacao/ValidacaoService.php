<?php

namespace App\Services\Validacao;

use Illuminate\Support\Fluent;

class ValidacaoService
{

    public function CPFValidacao(Fluent $requestData) : Fluent
    {
        $texto = $requestData->texto;
        $isValid = CPFValidacao::executa($texto);
        return new Fluent(['valido' => $isValid]);
    }

    public function CNPJValidacao(Fluent $requestData) : Fluent
    {
        $texto = $requestData->texto;
        $isValid = CNPJValidacao::executa($texto);
        return new Fluent(['valido' => $isValid]);
    }

}
