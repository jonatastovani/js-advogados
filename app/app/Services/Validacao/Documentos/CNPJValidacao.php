<?php

namespace App\Services\Validacao\Documentos;

class CNPJValidacao
{
    /**
     * Valida um número de CNPJ.
     *
     * @param string $cnpj O número do CNPJ a ser validado.
     * @return bool Retorna true se o CNPJ for válido, caso contrário, false.
     */
    public static function executa($cnpj): bool
    {
        // Remove todos os caracteres que não sejam dígitos
        $cnpj = preg_replace('/\D/', '', $cnpj);

        // Verifica se o CNPJ tem 14 dígitos ou é uma sequência repetida
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Função para calcular o dígito verificador
        $calculaDigito = function ($cnpj, $tamanho) {
            $soma = 0;
            $peso = ($tamanho === 12) ? 5 : 6;

            for ($c = 0; $c < $tamanho; $c++) {
                $soma += $cnpj[$c] * $peso;
                $peso = ($peso === 2) ? 9 : $peso - 1;
            }

            $resto = $soma % 11;
            return $resto < 2 ? 0 : 11 - $resto;
        };

        // Calcula os dois dígitos verificadores
        $digito1 = $calculaDigito($cnpj, 12);
        $digito2 = $calculaDigito($cnpj, 13);

        // Verifica se os dígitos calculados conferem com os do CNPJ
        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }
}
