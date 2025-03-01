<?php

namespace App\Helpers;

use App\Models\Referencias\DocumentoModeloTipo;
use Illuminate\Support\Fluent;

class DocumentoModeloQuillEditorHelper
{
    /**
     * Executa todas as verificações nas marcações do Quill.
     *
     * @param Fluent $data - Contém o conteúdo do Quill e a lista de objetos.
     * @return array - Retorna marcações sem referência, objetos não utilizados e objetos utilizados.
     */
    public static function verificarInconsistencias(Fluent $data): array
    {
        $conteudo = $data['conteudo'] ?? [];
        // Obtém os objetos enviados pela requisição
        $objetos = $data['objetos'] ?? [];

        // Extrai todas as marcações presentes no conteúdo do Quill
        $marcacoesNoTexto = self::extrairMarcacoes($conteudo['ops'] ?? []);

        // Busca o tipo do modelo de documento
        $documentoModeloTipo = DocumentoModeloTipo::find($data['documento_modelo_tipo_id']);

        // Obtém os objetos válidos que esse modelo permite
        $objetosBase = $documentoModeloTipo->objetos;

        // Renderiza os objetos prontos para verificação
        $objetosEnviados = self::renderizarObjetos($objetos, $objetosBase);

        // Extrai todas as marcações válidas da lista de objetos do tipo de modelo
        $marcacoesValidas = self::extrairMarcacoesValidasObjetos($objetosEnviados);

        // Verificar marcações sem referência
        $marcacoesSemReferencia = self::verificarMarcacoesSemReferencia($marcacoesNoTexto, $marcacoesValidas);

        // Verificar objetos não utilizados
        $objetosNaoUtilizados = self::verificarObjetosNaoUtilizados($marcacoesNoTexto, $objetosEnviados);

        // Verificar objetos utilizados e seus marcadores
        $objetosUtilizados = self::verificarObjetosUtilizados($marcacoesNoTexto, $objetosEnviados);

        return [
            'marcacoes_sem_referencia' => $marcacoesSemReferencia,
            'objetos_nao_utilizados' => $objetosNaoUtilizados,
            'objetos_utilizados' => collect($objetosUtilizados)->map(function ($objeto) {
                return [
                    'identificador' => $objeto['identificador'],
                    'contador' => $objeto['contador'],
                    'nome' => $objeto['nome'],
                    'marcadores_usados' => $objeto['marcadores_usados'],
                    'idAccordionNovoObjeto' => $objeto['idAccordionNovoObjeto'] ?? null
                ];
            })->toArray()
        ];
    }

    private static function renderizarObjetos(array $objetos, array $objetosBase): array
    {
        // Inicializa o array para armazenar os objetos prontos para verificação
        $objetosEnviados = [];

        foreach ($objetos as $objeto) {
            $identificador = $objeto['identificador'];
            $contador = $objeto['contador'];

            // Encontra o objeto válido correspondente
            $objetoBase = collect($objetosBase)->firstWhere('identificador', $identificador);

            if (!$objetoBase) {
                // Se o objeto não for permitido no modelo, pula para o próximo
                continue;
            }

            // Gera as marcações renderizadas com base no prefixo e contador
            $marcadoresRenderizados = array_map(function ($marcador) use ($objetoBase, $contador) {
                return [
                    'display' => $marcador['display'],
                    'sufixo' => $marcador['sufixo'],
                    'marcacao' => str_replace('{{contador}}', $contador, "{{{$objetoBase['marcador_prefixo']}.{$marcador['sufixo']}}}")
                ];
            }, $objetoBase['marcadores']);

            // Monta o objeto pronto para verificação
            $objetosEnviados[] = [
                'identificador' => $identificador,
                'contador' => $contador,
                'nome' => "{$identificador}.{$contador}",
                'marcadores' => $marcadoresRenderizados,
                'idAccordionNovoObjeto' => $objeto['idAccordionNovoObjeto'] ?? null
            ];
        }

        return $objetosEnviados;
    }

    /**
     * Extrai todas as marcações encontradas no texto do Quill.
     *
     * @param array $ops - Array de operações do Delta do Quill.
     * @return array - Lista de marcações encontradas.
     */
    private static function extrairMarcacoes(array $ops): array
    {
        $marcacoes = [];
        $regex = '/\{\{(.*?)\}\}/';

        foreach ($ops as $op) {
            if (isset($op['insert']) && is_string($op['insert'])) {
                preg_match_all($regex, $op['insert'], $matches);
                if (!empty($matches[0])) {
                    $marcacoes = array_merge($marcacoes, $matches[0]);
                }
            }
        }

        return array_unique($marcacoes); // Remover duplicatas
    }

    /**
     * Extrai todas as marcações válidas da lista de objetos.
     *
     * @param array $objetos - Lista de objetos e suas marcações.
     * @return array - Lista de marcações válidas.
     */
    private static function extrairMarcacoesValidasObjetos(array $objetos): array
    {
        $marcacoes = [];

        foreach ($objetos as $objeto) {
            if (isset($objeto['marcadores']) && is_array($objeto['marcadores'])) {
                foreach ($objeto['marcadores'] as $marcador) {
                    $marcacoes[] = $marcador['marcacao'] ?? '';
                }
            }
        }

        return array_unique(array_filter($marcacoes)); // Remover vazios e duplicatas
    }

    /**
     * Verifica quais marcações estão no texto mas não possuem referência.
     *
     * @param array $marcacoesNoTexto - Lista de marcações presentes no texto.
     * @param array $marcacoesValidas - Lista de marcações válidas da lista de clientes.
     * @return array - Lista de marcações sem referência.
     */
    private static function verificarMarcacoesSemReferencia(array $marcacoesNoTexto, array $marcacoesValidas): array
    {
        $contadorMarcacoes = [];
        $marcacoesSemReferencia = [];

        foreach ($marcacoesNoTexto as $marcacao) {
            if (!in_array($marcacao, $marcacoesValidas)) {
                $contadorMarcacoes[$marcacao] = ($contadorMarcacoes[$marcacao] ?? 0) + 1;

                $marcacoesSemReferencia[] = [
                    'marcacao' => $marcacao,
                    'indice' => $contadorMarcacoes[$marcacao], // Index da repetição
                ];
            }
        }

        return $marcacoesSemReferencia;
    }

    // /**
    //  * Verifica quais marcações estão no texto mas não possuem referência válida.
    //  *
    //  * @param array $marcacoesNoTexto - Lista de marcações presentes no texto.
    //  * @param array $objetosValidos - Lista de objetos válidos para este tipo de modelo.
    //  * @return array - Lista de marcações sem referência.
    //  */
    // private static function verificarMarcacoesSemReferencia(array $marcacoesNoTexto, array $objetosValidos): array
    // {
    //     $contadorMarcacoes = [];
    //     $marcacoesSemReferencia = [];

    //     foreach ($marcacoesNoTexto as $marcacao) {
    //         // Extrair identificador e contador da marcação
    //         preg_match('/\{\{([a-zA-Z]+)\.(\d+)\.(.*?)\}\}/', $marcacao, $matches);

    //         if (count($matches) < 4) {
    //             // Se a marcação não segue o padrão esperado, já adicionamos como sem referência
    //             $contadorMarcacoes[$marcacao] = ($contadorMarcacoes[$marcacao] ?? 0) + 1;
    //             $marcacoesSemReferencia[] = [
    //                 'marcacao' => $marcacao,
    //                 'indice' => $contadorMarcacoes[$marcacao]
    //             ];
    //             continue;
    //         }

    //         // Captura os componentes da marcação
    //         $inicioMarcadorPrefixo = $matches[1]; // Exemplo: clientePF
    //         $contador = $matches[2] ?: '1'; // Se não houver número, assume 1
    //         $sufixo = $matches[3]; // Exemplo: nome, cpf

    //         // Verifica se o inicioMarcadorPrefixo é válido com base nos objetos permitidos
    //         $objetoValido = array_filter($objetosValidos, function ($obj) use ($inicioMarcadorPrefixo) {
    //             preg_match('/^([a-zA-Z]+)\.\{\{contador\}\}$/', $obj['marcador_prefixo'], $objMatches);

    //             // Evita erro se `preg_match` falhar
    //             if (!isset($objMatches[1])) {
    //                 Log::info('Falha ao extrair identificador do marcador_prefixo: ' . json_encode(['marcador_prefixo' => $obj['marcador_prefixo'], 'objMatches' => $objMatches]));
    //                 return false;
    //             }

    //             return $objMatches[1] === $inicioMarcadorPrefixo;
    //         });

    //         if (!$objetoValido) {
    //             // Se o objeto não for permitido, adiciona à lista de marcações sem referência
    //             $contadorMarcacoes[$marcacao] = ($contadorMarcacoes[$marcacao] ?? 0) + 1;
    //             $marcacoesSemReferencia[] = [
    //                 'marcacao' => $marcacao,
    //                 'indice' => $contadorMarcacoes[$marcacao]
    //             ];
    //             continue;
    //         }

    //         // Obtém os marcadores válidos desse objeto
    //         $objetoValido = array_values($objetoValido)[0]; // Pega o primeiro resultado
    //         $marcadoresPermitidos = array_column($objetoValido['marcadores'], 'sufixo');

    //         // Verifica se o marcador está presente na lista de marcadores permitidos
    //         $marcadorCompleto = "{$objetoValido['marcador_prefixo']}.{$sufixo}";
    //         $marcadorCompleto = str_replace('{{contador}}', $contador, $marcadorCompleto);

    //         if (!in_array($sufixo, $marcadoresPermitidos)) {
    //             // Se o marcador não for permitido, adiciona à lista de marcações sem referência
    //             $contadorMarcacoes[$marcacao] = ($contadorMarcacoes[$marcacao] ?? 0) + 1;
    //             $marcacoesSemReferencia[] = [
    //                 'marcacao' => $marcacao,
    //                 'indice' => $contadorMarcacoes[$marcacao]
    //             ];
    //         }
    //     }

    //     return $marcacoesSemReferencia;
    // }

    /**
     * Verifica quais objetos (clientes) foram inseridos mas não possuem marcações utilizadas no texto.
     *
     * @param array $marcacoesNoTexto - Lista de marcações presentes no texto.
     * @param array $objetos - Lista de objetos.
     * @return array - Lista de objetos não utilizados.
     */
    private static function verificarObjetosNaoUtilizados(array $marcacoesNoTexto, array $objetos): array
    {
        return array_values(array_filter($objetos, function ($objeto) use ($marcacoesNoTexto) {
            $marcacoesDoObjeto = array_column($objeto['marcadores'] ?? [], 'marcacao');
            return empty(array_intersect($marcacoesNoTexto, $marcacoesDoObjeto));
        }));
    }

    /**
     * Verifica quais objetos (clientes) foram utilizados no texto e adiciona os marcadores usados.
     *
     * @param array $marcacoesNoTexto - Lista de marcações presentes no texto.
     * @param array $objetos - Lista de objetos.
     * @return array - Lista de objetos utilizados, incluindo `marcadores_usados`.
     */
    private static function verificarObjetosUtilizados(array $marcacoesNoTexto, array $objetos): array
    {
        return array_values(array_filter(array_map(function ($objeto) use ($marcacoesNoTexto) {
            $marcadoresUsados = array_values(array_filter($objeto['marcadores'] ?? [], function ($marcador) use ($marcacoesNoTexto) {
                return in_array($marcador['marcacao'], $marcacoesNoTexto);
            }));

            return !empty($marcadoresUsados) ? array_merge($objeto, ['marcadores_usados' => $marcadoresUsados]) : null;
        }, $objetos)));
    }
}
