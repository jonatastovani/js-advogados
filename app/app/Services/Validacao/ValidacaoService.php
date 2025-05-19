<?php

namespace App\Services\Validacao;

use App\Services\Validacao\Documentos\ChavePixValidacao;
use App\Services\Validacao\Documentos\CNPJValidacao;
use App\Services\Validacao\Documentos\CPFValidacao;
use Illuminate\Support\Fluent;

class ValidacaoService
{

    public function CPFValidacao(Fluent $requestData, $options = []): array
    {
        $isValid = CPFValidacao::executa($requestData->numero);
        $mensagemValido = "CPF válido.";
        $mensagemInvalido = "O CPF informado é inválido.";

        return [
            'valido' => $isValid,
            'mensagem' => $isValid ? $mensagemValido : $mensagemInvalido,
        ];
    }

    public function CNPJValidacao(Fluent $requestData, $options = []): array
    {
        $isValid = CNPJValidacao::executa($requestData->numero);
        $mensagemValido = "CNPJ válido.";
        $mensagemInvalido = "O CNPJ informado é inválido.";

        return [
            'valido' => $isValid,
            'mensagem' => $isValid ? $mensagemValido : $mensagemInvalido,
        ];
    }

    public function ChavePixValidacao(Fluent $requestData, $options = []): array
    {
        $isValid = ChavePixValidacao::executa($requestData->numero, $requestData->tipo_chave);
        $mensagemValido = "Chave Pix válida.";
        $mensagemInvalido = "A chave Pix informada é inválida.";

        return [
            'valido' => $isValid,
            'mensagem' => $isValid ? $mensagemValido : $mensagemInvalido,
        ];
    }
}
