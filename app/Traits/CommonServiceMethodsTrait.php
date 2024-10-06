<?php

namespace App\Traits;

trait CommonServiceMethodsTrait
{
    protected function tratamentoDeTextoPorTipoDeCampo(string $texto, array $dados): array
    {
        $tratamento = $dados['tratamento'] ?? [];
        $campo = $dados['campo'] ?? '';
        // echo "Texto: $texto <br>";
        // echo "Tratamento: <pre>";
        // print_r($tratamento);
        // echo "</pre>";

        if (isset($tratamento['personalizado'])) {
            switch ($tratamento['personalizado']) {
                case 'matricula_e_documento':
                    // Remove espaços e pontuação do texto e do campo
                    $texto = preg_replace('/\s+/', '', $texto);
                    $texto = preg_replace('/[^\w]/', '', $texto);
                    $campo = "regexp_replace(CAST($campo AS TEXT), '[[:punct:]]', '', 'g')";
                    break;

                default:
                    break;
            }
        }

        if (isset($tratamento['remove_espacos']) && $tratamento['remove_espacos']) {
            // Remove espaços do texto e do campo
            $texto = preg_replace('/\s+/', '', $texto);
            $campo = "regexp_replace(CAST($campo AS TEXT), '\\s+', '', 'g')";
        }

        if (isset($tratamento['maiusculo']) && $tratamento['maiusculo']) {
            // Converte o texto e o campo para maiúsculas
            $texto = strtoupper($texto);
            $campo = "UPPER(CAST($campo AS TEXT))";
        }

        if (isset($tratamento['minusculo']) && $tratamento['minusculo']) {
            // Converte o texto e o campo para minúsculas
            $texto = strtolower($texto);
            $campo = "LOWER(CAST($campo AS TEXT))";
        }

        return ['texto' => $texto, 'campo' => $campo];
    }

    protected function tratamentoCamposTraducao(array $arrayCampos, array $arrayCamposPadroes, array $dados): array
    {
        $todosCampos = $dados['campos_busca_todos'] ?? false;
        $camposRequisitados = !$todosCampos ? ($dados['campos_busca'] ?? $arrayCamposPadroes) : [];
        $retorno = [];

        foreach ($arrayCampos as $key => $value) {
            if (in_array($key, $camposRequisitados) || $todosCampos) {
                $retorno[] = $value;
            }
        }

        return $retorno;
    }
}
