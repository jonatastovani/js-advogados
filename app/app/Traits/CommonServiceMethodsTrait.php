<?php

namespace App\Traits;

use App\Helpers\StringHelper;

/**
 * Trait que fornece métodos auxiliares comuns para serviços.
 */
trait CommonServiceMethodsTrait
{
    /**
     * Realiza o tratamento do texto e do campo baseado nas regras definidas no array de dados.
     * Utilizado para gerar filtros com regras específicas como remoção de espaços, pontuação, 
     * conversão para maiúsculo/minúsculo ou tratamento personalizado.
     *
     * @param string $texto Texto original da busca.
     * @param array $dados Array contendo o campo e as regras de tratamento.
     *     - 'campo': nome do campo no banco.
     *     - 'tratamento': array com as regras, podendo conter:
     *         - 'personalizado': 'documento' (remove pontuações).
     *         - 'remove_espacos': true.
     *         - 'maiusculo': true.
     *         - 'minusculo': true.
     *
     * @return array Retorna um array com:
     *     - 'texto': texto tratado.
     *     - 'campo': expressão SQL para o campo tratado.
     */
    protected function tratamentoDeTextoPorTipoDeCampo(string $texto, array $dados): array
    {
        $tratamento = $dados['tratamento'] ?? [];
        $campo = $dados['campo'] ?? '';

        if (isset($tratamento['personalizado'])) {
            switch ($tratamento['personalizado']) {
                case 'documento':
                    // Remove espaços e pontuação do texto e do campo
                    $texto = preg_replace('/\s+/', '', $texto);
                    $texto = preg_replace('/[^\w]/', '', $texto);
                    $campo = "regexp_replace(CAST($campo AS TEXT), '[[:punct:]]', '', 'g')";
                    break;
                default:
                    // Outros tratamentos personalizados podem ser adicionados aqui futuramente
                    break;
            }
        }

        if (!empty($tratamento['remove_espacos'])) {
            // Remove espaços do texto e do campo
            $texto = preg_replace('/\s+/', '', $texto);
            $campo = "regexp_replace(CAST($campo AS TEXT), '\\s+', '', 'g')";
        }

        if (!empty($tratamento['maiusculo'])) {
            // Remove acentos e converte o texto para maiúsculo
            $texto = mb_strtoupper(StringHelper::removeAccents($texto));
            [$originais, $semAcento] = StringHelper::getTranslatePostgresAcentos();
            $campo = "TRANSLATE(UPPER(CAST($campo AS TEXT)), '{$originais}', '{$semAcento}')";
        } elseif (!empty($tratamento['minusculo'])) {
            // Remove acentos e converte o texto para minúsculo
            $texto = mb_strtolower(StringHelper::removeAccents($texto));
            [$originais, $semAcento] = StringHelper::getTranslatePostgresAcentos();
            $campo = "TRANSLATE(LOWER(CAST($campo AS TEXT)), '{$originais}', '{$semAcento}')";
        }

        return ['texto' => $texto, 'campo' => $campo];
    }

    /**
     * Traduz os campos informados no array para os campos que devem ser utilizados no filtro,
     * com base na configuração de filtros recebidos.
     *
     * @param array $arrayCampos Campos disponíveis para filtro (key => config).
     * @param array $arrayCamposPadroes Campos padrão a serem utilizados caso nenhum seja informado.
     * @param array $dados Dados recebidos contendo a configuração de quais campos buscar.
     *     - 'campos_busca': campos específicos informados.
     *     - 'campos_busca_todos': se verdadeiro, ignora filtro de campos.
     *
     * @return array Campos traduzidos prontos para aplicar filtro.
     */
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
     * Mescla dois arrays de relacionamentos, aplicando opções de remoção, substituição e prefixação.
     * Utilizado para compor dinamicamente as relações carregadas em services.
     *
     * @param array $relationships Array principal de relacionamentos.
     * @param array $additionalRelationships Array adicional a ser mesclado.
     * @param array $options Opções de manipulação:
     *     - 'removePrefix': prefixos para excluir totalmente.
     *     - 'removeExact': nomes exatos para excluir.
     *     - 'stripPrefix': prefixos a remover mantendo o restante.
     *     - 'addPrefix': prefixo a adicionar nos valores restantes.
     * @return array Array final mesclado e tratado.
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
