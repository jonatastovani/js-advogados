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

    /**
     * Carrega relacionamentos de outra service, com manipulação dinâmica.
     *
     * @param array $relationships Array base de relacionamentos.
     * @param array $additionalRelationships Array de relacionamentos adicionais de outra service.
     * @param array $options Opções para manipulação de relacionamentos.
     *     - 'removePrefix' (array|string|null): Itens a serem removidos completamente com base nos prefixos.
     *     - 'removeExact' (array|string|null): Nomes completos a serem removidos.
     *     - 'stripPrefix' (array|string|null): Prefixos a serem removidos, mantendo o restante do valor.
     *     - 'addPrefix' (string|null): Prefixo a ser adicionado.
     * @return array Array combinado e manipulado de relacionamentos.
     */
    protected function mergeRelationships(array $relationships, array $additionalRelationships, array $options = []): array
    {
        $removePrefixes = (array)($options['removePrefix'] ?? []);
        $removeExact = (array)($options['removeExact'] ?? []);
        $stripPrefixes = (array)($options['stripPrefix'] ?? []);
        $addPrefix = $options['addPrefix'] ?? null;

        // Remove itens com os prefixos especificados
        foreach ($removePrefixes as $prefix) {
            $additionalRelationships = array_filter($additionalRelationships, function ($item) use ($prefix) {
                return !str_starts_with($item, $prefix);
            });
        }

        // Remove itens com os nomes completos especificados
        foreach ($removeExact as $exact) {
            $additionalRelationships = array_filter($additionalRelationships, function ($item) use ($exact) {
                return $item !== $exact;
            });
        }

        // Remove somente o prefixo, mantendo o restante do valor
        foreach ($stripPrefixes as $prefix) {
            $additionalRelationships = array_map(function ($item) use ($prefix) {
                return str_starts_with($item, $prefix) ? substr($item, strlen($prefix)) : $item;
            }, $additionalRelationships);
        }

        // Adiciona o prefixo especificado
        if ($addPrefix) {
            $additionalRelationships = array_map(function ($item) use ($addPrefix) {
                return $addPrefix . $item;
            }, $additionalRelationships);
        }

        // Mescla os arrays sem duplicar
        return array_unique(array_merge($relationships, $additionalRelationships));
    }
}
