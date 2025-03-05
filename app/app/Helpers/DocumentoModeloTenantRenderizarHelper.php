<?php

namespace App\Helpers;

use App\Common\RestResponse;
use App\Models\Tenant\DocumentoModeloTenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class DocumentoModeloTenantRenderizarHelper
{
    /**
     * Verifica inconsistências nos objetos vinculados e se possuem todos os campos necessários.
     *
     * @param Fluent $data
     * @return array
     */
    public static function verificarInconsistencias(Fluent $data): array
    {
        // Busca o tipo do modelo de documento
        $documentoModeloTenant = DocumentoModeloTenant::find($data['documento_modelo_tenant_id']);

        // Obtém os objetos enviados pela requisição, onde tem os objetos e os dados vinculados
        $objetosVinculados = $data['objetos_vinculados'] ?? [];

        // Normalizar os objetos vinculados
        foreach ($objetosVinculados as $key => $objeto) {
            $objetosVinculados[$key]['identificador'] = $objeto['objeto_vinculado']['identificador'];
            $objetosVinculados[$key]['contador'] = $objeto['objeto_vinculado']['contador'];
        }

        $objetosNaoVinculados = [];
        $objetosCamposAusentes = [];

        // Renderiza os objetos vinculados conforme modelo
        $objetosVinculadosBase = DocumentoModeloQuillEditorHelper::renderizarObjetos($objetosVinculados, $documentoModeloTenant->documento_modelo_tipo->objetos);

        // Extrai todas as marcações presentes no conteúdo do Quill
        $marcacoesNoTextoModelo = DocumentoModeloQuillEditorHelper::extrairMarcacoes($documentoModeloTenant['conteudo']['ops']);

        // Verificar objetos utilizados e seus marcadores
        $objetosVinculadosUtilizados = DocumentoModeloQuillEditorHelper::verificarObjetosUtilizados($marcacoesNoTextoModelo, $objetosVinculadosBase);

        // Objetos requisitados no modelo de documento
        $objetosModeloRequisitados = DocumentoModeloQuillEditorHelper::renderizarObjetos($documentoModeloTenant->objetos, $documentoModeloTenant->documento_modelo_tipo->objetos);

        // **VERIFICAR OBJETOS NÃO VINCULADOS**
        $objetosNaoVinculados = self::verificarObjetosNaoVinculados($objetosModeloRequisitados, $objetosVinculadosUtilizados);

        // **VERIFICAR CAMPOS AUSENTES NOS OBJETOS VINCULADOS**
        foreach ($objetosVinculados as $objeto) {
            $dados = $objeto['dados'] ?? [];
            $objetoModelo = collect($objetosVinculadosUtilizados)->where('identificador', $objeto['identificador'])->where('contador', $objeto['contador'])->first();

            if (!$objetoModelo) {
                continue; // Se o objeto não está na lista de permitidos, ignora
            }

            $marcadoresUsados = $objetoModelo['marcadores_usados'];
            $camposAusentes = self::verificarCamposFaltantes($dados, $marcadoresUsados);

            if (!empty($camposAusentes)) {
                $objetosCamposAusentes[] = array_merge($objeto, [
                    'nome' => $objetoModelo['nome'],
                    'campos_faltantes' => $camposAusentes
                ]);
            }
        }

        // Adicionar campo dados nos objetos vinculados utilizados
        foreach ($objetosVinculadosUtilizados as $key => $objeto) {
            $objetosVinculadosUtilizados[$key] = array_merge(collect($objetosVinculados)
                ->where('contador', $objeto['contador'])->where('identificador', $objeto['identificador'])->first(), $objeto);
        }

        // Vincular os marcadores aos valores extraídos dos dados
        $objetosVinculadosUtilizados = self::mapearValoresDosMarcadores($objetosVinculadosUtilizados);

        return [
            'objetos_nao_vinculados' => collect($objetosNaoVinculados)->sortBy('contador')->values()->toArray(),
            'objetos_campos_ausentes' => collect($objetosCamposAusentes)->sortBy('contador')->values()->toArray(),
            // 'objetos_vinculados_utilizados' => collect($objetosVinculadosUtilizados)->sortBy('contador')->values()->toArray(),
            // 'objetos_vinculados' => collect($objetosVinculados)->sortBy('contador')->values()->toArray(),
            'conteudo' => self::substituirMarcadoresNoOps($documentoModeloTenant['conteudo'], $objetosVinculadosUtilizados),
        ];
    }

    /**
     * Verifica quais objetos foram requisitados pelo modelo, mas não foram vinculados na requisição.
     *
     * @param array $objetosModeloRequisitados - Lista de objetos esperados pelo modelo.
     * @param array $objetosVinculadosUtilizados - Lista de objetos já vinculados.
     * @return array - Retorna a lista de objetos não vinculados.
     */
    private static function verificarObjetosNaoVinculados(array $objetosModeloRequisitados, array $objetosVinculadosUtilizados): array
    {
        return array_values(array_filter($objetosModeloRequisitados, function ($objRequisitado) use ($objetosVinculadosUtilizados) {
            return !collect($objetosVinculadosUtilizados)->firstWhere(function ($objVinculado) use ($objRequisitado) {
                return $objVinculado['identificador'] === $objRequisitado['identificador'] &&
                    $objVinculado['contador'] === $objRequisitado['contador'];
            });
        }));
    }

    /**
     * Verifica quais campos obrigatórios estão faltando nos dados do objeto.
     *
     * @param array $dados - Os dados do objeto vinculado.
     * @param array $marcadoresEsperados - Lista de campos esperados.
     * @return array - Retorna os campos ausentes.
     */
    private static function verificarCamposFaltantes(array $dados, array $marcadoresEsperados): array
    {
        $camposFaltantes = [];

        foreach ($marcadoresEsperados as $marcador) {
            $sufixo = $marcador['sufixo'];

            // Se o campo for aninhado (ex: endereco.logradouro, documento.numero)
            if (str_contains($sufixo, '.')) {
                if (!self::verificarCampoAninhado($dados, explode('.', $sufixo))) {
                    $camposFaltantes[] = $marcador;
                }
            } else {
                // Campo direto no objeto
                if (!isset($dados[$sufixo]) || empty($dados[$sufixo])) {
                    $camposFaltantes[] = $marcador;
                }
            }
        }

        return $camposFaltantes;
    }

    /**
     * Verifica recursivamente se um campo aninhado existe no array.
     *
     * @param array $dados - Os dados do objeto.
     * @param array $caminho - Array representando o caminho do campo (ex: ['endereco', 'logradouro']).
     * @return bool - Retorna true se o campo existe e tem valor, false se estiver ausente ou vazio.
     */
    private static function verificarCampoAninhado(array $dados, array $caminho): bool
    {
        // Remove o primeiro elemento do caminho (nível atual que estamos verificando)
        $campoAtual = array_shift($caminho);

        // Verifica se o campo atual existe no array de dados
        if (!isset($dados[$campoAtual])) {
            return false; // Retorna falso se o campo não existir
        }

        // Se chegamos ao último nível do caminho, verificar se o campo está preenchido
        if (empty($caminho)) {
            return !empty($dados[$campoAtual]); // Retorna verdadeiro se o campo não estiver vazio
        }

        // Se o campo é um array, iteramos sobre os subitens para buscar a próxima chave
        if (is_array($dados[$campoAtual])) {

            foreach ($dados[$campoAtual] as $key => $subItem) {

                // Se o subitem for um array, chama recursivamente para verificar os próximos níveis
                if (is_array($subItem) && self::verificarCampoAninhado($subItem, $caminho)) {
                    return true; // Retorna verdadeiro se encontrar um campo preenchido em qualquer subnível
                }

                // Se não for um array, verifica se o próximo item do caminho corresponde a uma chave existente
                if ($key == $caminho[0]) {
                    return !empty($dados[$campoAtual][$key]); // Retorna verdadeiro se o valor não estiver vazio
                }
            }

            return false; // Retorna falso se nenhum dos subitens possuir o campo preenchido
        }

        return false; // Retorna falso se o campo atual não for um array ou não tiver mais níveis para percorrer
    }

    /**
     * Mapeia os valores dos marcadores usados em cada objeto vinculado.
     *
     * @param array $objetosVinculadosUtilizados - Lista de objetos vinculados e seus marcadores usados.
     * @return array - Retorna um array contendo cada marcador e seu respectivo valor extraído de 'dados'.
     */
    private static function mapearValoresDosMarcadores(array $objetosVinculadosUtilizados): array
    {
        foreach ($objetosVinculadosUtilizados as &$objeto) {
            $objeto['valores_mapeados'] = [];

            if (!isset($objeto['dados'])) {
                continue; // Ignora objetos sem dados vinculados
            }

            foreach ($objeto['marcadores_usados'] as $marcador) {
                // Obtém o valor correspondente ao marcador dentro de 'dados'
                $valorExtraido = self::obterValorAninhado($objeto['dados'], explode('.', $marcador['sufixo']));

                // Adiciona a entrada ao array de valores mapeados
                $objeto['valores_mapeados'][] = [
                    'marcador' => $marcador['marcacao'],
                    'valor' => $valorExtraido ?? '---' // Se não existir, coloca um valor padrão
                ];
            }
        }

        return $objetosVinculadosUtilizados;
    }

    /**
     * Obtém um valor aninhado dentro de 'dados' baseado no caminho do marcador.
     *
     * @param array $dados - Os dados do objeto.
     * @param array $caminho - Array representando o caminho do campo (ex: ['endereco', 'logradouro']).
     * @return mixed - Retorna o valor encontrado ou null se não existir.
     */
    private static function obterValorAninhado(array $dados, array $caminho)
    {
        // Remove o primeiro elemento do caminho (nível atual que estamos verificando)
        $campoAtual = array_shift($caminho);

        // Verifica se o campo atual existe no array de dados
        if (!isset($dados[$campoAtual])) {
            return null; // Retorna null se o campo não existir
        }

        // Se chegamos ao último nível do caminho, retorna o valor encontrado
        if (empty($caminho)) {
            return !empty($dados[$campoAtual]) ? $dados[$campoAtual] : null;
        }

        // Se o campo for um array, verifica recursivamente
        if (is_array($dados[$campoAtual])) {
            foreach ($dados[$campoAtual] as $key => $subItem) {
                // Se o índice da iteração for o próximo item no caminho, chama recursivamente
                if (is_array($subItem)) {
                    $valor = self::obterValorAninhado($subItem, $caminho);
                    if ($valor !== null) {
                        return $valor;
                    }
                }

                if ($key == $caminho[0]) {
                    $valor = $dados[$campoAtual][$key];
                    if ($valor !== null) {
                        return $valor;
                    }
                }
            }
        }

        return null; // Retorna null se não encontrou o valor
    }

    /**
     * Substitui os marcadores nos conteúdos do documento Quill pelos valores reais mapeados.
     *
     * @param array $conteudo - O conteúdo do modelo.
     * @param array $objetosVinculadosUtilizados - Lista de objetos vinculados com seus valores mapeados.
     * @return array - Retorna o conteúdo do documento com os valores substituídos.
     */
    private static function substituirMarcadoresNoOps(array $conteudo, array $objetosVinculadosUtilizados): array
    {
        // Criamos um array de substituições onde a chave é o marcador e o valor é o respectivo valor mapeado
        $substituicoes = [];

        foreach ($objetosVinculadosUtilizados as $objeto) {
            foreach ($objeto['valores_mapeados'] as $mapeado) {
                $substituicoes[$mapeado['marcador']] = $mapeado['valor'] ?? '---'; // Se o valor for null, substitui por '---'
            }
        }

        // Itera sobre os blocos de texto no conteúdo do documento (Quill ops)
        foreach ($conteudo['ops'] as &$op) {

            if (isset($op['insert']) && is_string($op['insert'])) {

                // Verifica se o texto contém algum dos marcadores antes de substituir
                foreach ($substituicoes as $marcador => $valor) {

                    if (str_contains($op['insert'], $marcador)) {
                        $op['insert'] = str_replace($marcador, $valor, $op['insert']);
                    }
                }
            }
        }

        return $conteudo;
    }

    
}
