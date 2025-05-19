<?php

namespace App\Services\Validacao\Documentos;

class CPFValidacao
{
    /**
     * Valida um CPF.
     *
     * @param string $cpf O CPF a ser validado.
     * @return bool Retorna true se o CPF for válido, caso contrário, false.
     */
    public static function executa($cpf): bool
    {
        // Remove todos os caracteres que não sejam dígitos
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se o CPF tem 11 dígitos ou é uma sequência repetida
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Função para calcular o dígito verificador
        $calculaDigito = function ($cpf, $tamanho) {
            $soma = 0;
            for ($c = 0; $c < $tamanho; $c++) {
                $soma += $cpf[$c] * (($tamanho + 1) - $c);
            }
            $resto = ($soma * 10) % 11;
            return $resto === 10 ? 0 : $resto;
        };

        // Calcula os dois dígitos verificadores
        $digito1 = $calculaDigito($cpf, 9);
        $digito2 = $calculaDigito($cpf, 10);

        // Verifica se os dígitos calculados conferem com os do CPF
        return $cpf[9] == $digito1 && $cpf[10] == $digito2;
    }
}
