<?php

namespace App\Services\Validacao\Documentos;

class CNPJValidacao
{
    public static function executa($cnpj)
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Valida tamanho e números repetidos
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Cálculo dos dígitos verificadores
        for ($t = 12; $t < 14; $t++) {
            $d = 0; // Soma dos produtos
            $p = ($t == 12) ? 5 : 6; // Posição inicial

            for ($c = 0; $c < $t; $c++) {
                $d += $cnpj[$c] * $p;
                $p = ($p == 2) ? 9 : $p - 1;
            }

            $d = ($d % 11) < 2 ? 0 : 11 - ($d % 11);

            if ($cnpj[$t] != $d) { // Comparação correta com o índice $t
                return false;
            }
        }

        return true;
    }
}
