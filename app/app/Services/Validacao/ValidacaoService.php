<?php

namespace App\Services\Validacao;

use App\Services\Validacao\Documentos\CNPJValidacao;
use App\Services\Validacao\Documentos\CPFValidacao;
use Illuminate\Support\Fluent;

class ValidacaoService
{

    public function CPFValidacao(Fluent $requestData, $options = []): array
    {
        $texto = $requestData->texto;
        $isValid = CPFValidacao::executa($texto);
        $mensagem = "CPF válido.";

        if(!$isValid){
            $mensagem = "O CPF informado é inválido.";
        }
        return [
            'valido' => $isValid,
            'mensagem'=> $mensagem,
        ];
    }

    public function CNPJValidacao(Fluent $requestData, $options = []): array
    {
        $texto = $requestData->texto;
        $isValid = CNPJValidacao::executa($texto);
        $mensagem = "CNPJ válido.";

        if(!$isValid){
            $mensagem = "O CNPJ informado é inválido.";
        }
        return [
            'valido' => $isValid,
            'mensagem'=> $mensagem,
        ];
    }
}
