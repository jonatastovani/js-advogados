<?php

namespace App\Helpers;

use App\Common\CommonsFunctions;
use Illuminate\Support\Fluent;

class LogHelper
{
    /**
     * Gerar um log com campos dinâmicos e retornar um array de erro com trace ID.
     *
     * @param int $codigo Código de erro HTTP (obrigatório).
     * @param string $mensagem Mensagem de erro (obrigatório).
     * @param \Illuminate\Http\Request|Fluent $request Request atual com os dados enviados.
     * @param array $camposAdicionais Campos adicionais chave-valor para inserir no log.
     * @return array|Fluent Retorna um array com o erro, trace_id e quaisquer outros campos adicionais.
     */
    public static function gerarLogDinamico(int $codigo, string $mensagem, $request, array $camposAdicionais = []): Fluent
    {
        // Campos base obrigatórios
        $logData = [
            'codigo' => $codigo,
            'mensagem' => $mensagem,
            'request' => $request->toArray(),  // Incluindo os dados do request
        ];

        // Adicionar campos adicionais dinamicamente ao log
        foreach ($camposAdicionais as $chave => $valor) {
            $logData[$chave] = $valor;
        }

        // Gerar o log com todos os campos
        $traceId = CommonsFunctions::generateLog(
            json_encode($logData)
        );

        // Retornar o fluent com os campos de erro e trace ID, junto com os campos adicionais
        return new Fluent(array_merge([
            'error' => $mensagem,
            'trace_id' => $traceId,
        ], $camposAdicionais));
    }

    /**
     * Recebe um array de consultas executadas pelo Laravel e retorna um array com as consultas formatadas,
     * substituindo os placeholders '?' pelas variáveis que foram passadas para a consulta.
     *
     * @param array $queries Array de consultas executadas pelo Laravel
     * @return array Array de consultas formatadas
     */
    public static function formatQueryLog($queries)
    {
        return collect($queries)->map(function ($query) {
            $sql = $query['query'];
            foreach ($query['bindings'] as $binding) {
                $sql = preg_replace('/\?/', is_numeric($binding) ? $binding : "'{$binding}'", $sql, 1);
            }
            return $sql;
        })->toArray();
    }
}
