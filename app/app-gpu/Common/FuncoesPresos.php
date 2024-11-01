<?php

namespace App\Common;

use App\Models\IncEntradaPreso;
use App\Models\IncQualificativaProvisoria;
use App\Models\PresoPassagemArtigo;
use InvalidArgumentException;

class FuncoesPresos
{

    public static function retornaDigitoMatricula($matricula): int
    {
        $matricula = strval($matricula);

        $mult = 2;
        $soma = 0;
        $s = "";

        for ($i = strlen($matricula) - 1; $i >= 0; $i--) {
            $s = ($mult * intval($matricula[$i])) . $s;
            if (--$mult < 1) {
                $mult = 2;
            }
        }

        for ($i = 0; $i < strlen($s); $i++) {
            $soma = $soma + intval($s[$i]);
        }

        $soma = $soma % 10;

        if ($soma != 0) {
            $soma = 10 - $soma;
        }

        return intval($soma);
    }

    public static function retornaMatriculaFormatada($matricula, $tipo = 1, $inserirPontuacao = true)
    {
        // Verifica se o tipo é válido (1, 2 ou 3)
        if ($tipo < 1 || $tipo > 3) {
            throw new InvalidArgumentException('O tipo deve ser 1, 2 ou 3.');
        }
    
        // Remove qualquer pontuação existente na matrícula
        $matricula = preg_replace('/[^\d]/', '', $matricula);
    
        // Verifica se a matrícula tem pelo menos um número
        if (empty($matricula)) {
            throw new InvalidArgumentException('A matrícula deve conter pelo menos um número.');
        }
    
        $matricula = strval($matricula);
        $digito = substr($matricula, -1);
        $matricula = substr($matricula, 0, -1);

        if ($inserirPontuacao) {
            $matricula = number_format($matricula, 0, '', '.');
        }

        // Formata a matrícula conforme o tipo
        switch ($tipo) {
            case 1:
                $matriculaFormatada = $matricula . '-' . $digito;
                break;
    
            case 2:
                $matriculaFormatada = $matricula;
                break;
    
            case 3:
                $matriculaFormatada = $digito;
                break;
    
            default:
                throw new InvalidArgumentException('Tipo de formato inválido.');
        }
        
        return $matriculaFormatada;
    }

}
