<?php

namespace App\Helpers;

use App\Common\RestResponse;
use App\Models\Tenant\DocumentoModeloTenant;
use Illuminate\Database\Eloquent\JsonEncodingException;
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
        $objetos = $data['objetos_vinculados'] ?? [];

        // Faz a mesclagem dos campos objeto_vinculado e identificador
        foreach ($objetos as $key => $objeto) {
            $objetos[$key]['identificador'] = $objeto['objeto_vinculado']['identificador'];
            $objetos[$key]['contador'] = $objeto['objeto_vinculado']['contador'];
        }

        $objetosNaoVinculados = [];
        $objetosCamposAusentes = [];

        $objetosBase = DocumentoModeloQuillEditorHelper::renderizarObjetos($objetos, $documentoModeloTenant->documento_modelo_tipo->objetos);

        Log::debug("objetos: " . json_encode($objetos));
        Log::debug("objetosBase: " . json_encode($objetosBase));

        foreach ($objetos as $objeto) {

            // Obtém a estrutura do objeto vinculado
            $dados = $objeto['dados'] ?? [];

            // Encontra os campos obrigatórios para este tipo de objeto
            $objetoModelo = collect($objetosBase)->where('identificador', $objeto['identificador'])->where('contador', $objeto['contador'])->first();
            Log::debug("objetoModelo " . json_encode($objetoModelo));

            if (!$objetoModelo) {
                continue; // Se o objeto não está na lista de permitidos, ignora
            }

            $marcadoresEsperados = $objetoModelo['marcadores'];

            // Verificar se os campos esperados existem nos dados do objeto
            $camposAusentes = self::verificarCamposFaltantes($dados, $marcadoresEsperados);

            if (!empty($camposAusentes)) {
                $objetosCamposAusentes[] = array_merge($objeto, [
                    'nome' => $objetoModelo['nome'],
                    'campos_faltantes' => $camposAusentes
                ]);
            }
        }

        // Retorna os resultados da verificação
        return [
            'objetos_nao_vinculados' => $objetosNaoVinculados,
            'objetos_campos_ausentes' => $objetosCamposAusentes
        ];
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

            // Para endereços e arrays aninhados
            if (strpos($sufixo, 'endereco.') === 0) {
                $campoEndereco = str_replace('endereco.', '', $sufixo);

                if (empty($dados['endereco']) || !self::verificarCampoEmArray($dados['endereco'], $campoEndereco)) {
                    $camposFaltantes[] = $marcador;
                }
                continue;
            }

            // Para campos diretos no objeto
            if (!isset($dados[$sufixo]) || empty($dados[$sufixo])) {
                $camposFaltantes[] = $marcador;
            }
        }

        return $camposFaltantes;
    }

    /**
     * Verifica se pelo menos um item no array possui o campo necessário.
     *
     * @param array $array - Lista de objetos.
     * @param string $campo - O campo esperado.
     * @return bool - Retorna true se pelo menos um objeto tiver o campo preenchido.
     */
    private static function verificarCampoEmArray(array $array, string $campo): bool
    {
        foreach ($array as $item) {
            if (isset($item[$campo]) && !empty($item[$campo])) {
                return true;
            }
        }
        return false;
    }
}
