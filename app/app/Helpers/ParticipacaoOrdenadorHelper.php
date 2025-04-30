<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Enums\PessoaTipoEnum;
use Illuminate\Support\Facades\Log;

class ParticipacaoOrdenadorHelper
{
    /**
     * Ordena os itens da estrutura paginada (ex: serviços) organizando os participantes e seus integrantes.
     *
     * @param array $data Estrutura com chave 'data' contendo os registros.
     * @param array $options Opções:
     *   - 'chaves_alvo' => array com chaves que contêm os participantes (ex: ['participantes']).
     *   - 'ordem' => 'asc' ou 'desc' (padrão: 'asc').
     * @return array Estrutura modificada com participantes ordenados.
     */
    public static function ordenar(array $data, array $options = []): array
    {
        $chavesAlvo = $options['chaves_alvo'] ?? ['participantes'];
        $ordemDescendente = ($options['ordem'] ?? 'asc') === 'desc';

        if (!isset($data['data']) || !is_array($data['data'])) {
            return $data;
        }

        foreach ($data['data'] as &$item) {
            static::ordenarRecursivo($item, $chavesAlvo, $ordemDescendente);
        }

        return $data;
    }

    /**
     * Ordena um único item contendo participantes (ex: um serviço isolado).
     *
     * @param array $item Item com participantes e possíveis subníveis.
     * @param array $chavesAlvo Quais chaves contêm os participantes.
     * @param string $ordem 'asc' ou 'desc'.
     * @return array Item com os participantes ordenados.
     */
    public static function ordenarItem(array $item, array $chavesAlvo = ['participantes'], string $ordem = 'asc'): array
    {
        $ordemDescendente = $ordem === 'desc';
        static::ordenarRecursivo($item, $chavesAlvo, $ordemDescendente);
        return $item;
    }

    /**
     * Ordena recursivamente os níveis da estrutura, atuando nos participantes e seus integrantes.
     *
     * @param array $item Item com dados potencialmente aninhados.
     * @param array $chavesAlvo Quais chaves contêm os participantes.
     * @param bool $ordemDescendente Se deve ordenar de forma descendente.
     * @return void
     */
    protected static function ordenarRecursivo(array &$item, array $chavesAlvo, bool $ordemDescendente): void
    {
        foreach ($chavesAlvo as $chave) {
            if (isset($item[$chave]) && is_array($item[$chave])) {
                $colecao = collect($item[$chave]);

                // Ordena os participantes
                $item[$chave] = static::ordenarPorTipo($colecao, $ordemDescendente)->map(function ($subItem) use ($chave, $ordemDescendente) {
                    // Se o participante for um grupo, ordena também os integrantes
                    if (
                        ($subItem['participacao_registro_tipo_id'] ?? null) == ParticipacaoRegistroTipoEnum::GRUPO->value &&
                        isset($subItem['integrantes']) && is_array($subItem['integrantes'])
                    ) {
                        $integrantes = collect($subItem['integrantes']);
                        $subItem['integrantes'] = static::ordenarPorTipo($integrantes, $ordemDescendente)->toArray();
                    }
                    return $subItem;
                })->toArray();
            }
        }

        // Aplica recursivamente em outros níveis da estrutura
        foreach ($item as &$sub) {
            if (is_array($sub)) {
                if (array_is_list($sub)) {
                    foreach ($sub as &$subItem) {
                        if (is_array($subItem)) {
                            static::ordenarRecursivo($subItem, $chavesAlvo, $ordemDescendente);
                        }
                    }
                } else {
                    static::ordenarRecursivo($sub, $chavesAlvo, $ordemDescendente);
                }
            }
        }
    }

    /**
     * Ordena uma coleção de participantes ou integrantes por tipo (física, jurídica, grupo) e nome.
     *
     * @param Collection $colecao Coleção de participantes ou integrantes.
     * @param bool $ordemDescendente Se a ordenação é descendente.
     * @return Collection Coleção ordenada.
     */
    protected static function ordenarPorTipo(Collection $colecao, bool $ordemDescendente): Collection
    {
        return $colecao->sortBy(
            function ($item) {
                $tipoRegistro = $item['participacao_registro_tipo_id'] ?? null;

                // Prefixo define prioridade de ordenação
                $prefixo = match (true) {
                    $tipoRegistro == ParticipacaoRegistroTipoEnum::PERFIL->value => match ($item['referencia']['pessoa']['pessoa_dados']['pessoa_dados_type'] ?? null) {
                        PessoaTipoEnum::PESSOA_FISICA->value => '0_',
                        PessoaTipoEnum::PESSOA_JURIDICA->value => '1_',
                        default => '1_',
                    },
                    $tipoRegistro == ParticipacaoRegistroTipoEnum::GRUPO->value => '2_',
                    default => '3_',
                };

                return $prefixo . static::getNomeOrdenacao($item);
            },
            SORT_REGULAR,
            $ordemDescendente
        )->values();
    }

    /**
     * Retorna o nome base para ordenação, baseado na prioridade:
     * nome_grupo > nome (PF) > nome_fantasia (PJ) > razao_social (PJ)
     *
     * @param array $item Participante ou integrante.
     * @return string Nome formatado para ordenação.
     */
    protected static function getNomeOrdenacao(array $item): string
    {
        if (!empty($item['nome_grupo'])) {
            return trim(mb_strtolower($item['nome_grupo']));
        }

        $dados = $item['referencia']['pessoa']['pessoa_dados'] ?? [];

        if (!empty($dados['nome'])) {
            return trim(mb_strtolower($dados['nome']));
        }

        if (!empty($dados['nome_fantasia'])) {
            return trim(mb_strtolower($dados['nome_fantasia']));
        }

        if (!empty($dados['razao_social'])) {
            return trim(mb_strtolower($dados['razao_social']));
        }

        return '';
    }
}
