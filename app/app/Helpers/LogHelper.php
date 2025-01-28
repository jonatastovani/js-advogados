<?php

namespace App\Helpers;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'request' => $request,  // Incluindo os dados do request
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
            'code' => $codigo,
            'error' => $mensagem,
            'trace_id' => $traceId,
        ], $camposAdicionais));
    }

    /**
     * Habilita o log de queries para que possam ser capturadas posteriormente
     * com o método getQueryLogERetorna.
     */
    public static function habilitaQueryLog()
    {
        // Habilita o log de queries
        DB::enableQueryLog();
    }


    /**
     * Retorna o log de consultas em formato de array.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getQueryLogERetorna($dentroDeTryCatch = false)
    {
        $queries = self::getQueryLog();
        $formattedQueries = self::formatQueryLog($queries);
        if ($dentroDeTryCatch) {
            throw new \Exception(json_encode($formattedQueries), 500);
        }
        return RestResponse::createTestResponse($formattedQueries);
    }

    public static function getQueryLog()
    {
        return DB::getQueryLog();
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

    /**
     * Monta um array no padrão esperado pelo Laravel Query Log a partir de uma SQL e seus bindings.
     *
     * @param string $sql A string da query SQL criada até o momento.
     * @param array $bindings Os valores de bindings associados à query.
     * @return array Array formatado para ser usado no método formatQueryLog.
     */
    public static function createQueryLogFormat($sql, $bindings)
    {
        return [
            [
                'query' => $sql,
                'bindings' => $bindings,
                'time' => 0, // O tempo de execução pode ser opcional para debug
            ]
        ];
    }

    public static function createAndFormatQueryLog($sql, $bindings)
    {
        $create = self::createQueryLogFormat($sql, $bindings);
        return self::formatQueryLog($create);
    }

    public static function escreverLogSomenteComQuery(Builder $query)
    {
        $logFormat = self::createQueryLogFormat($query->toSql(), $query->getBindings());
        $logFormat = self::formatQueryLog($logFormat);

        foreach ($logFormat as $key => $value) {
            Log::debug("Query: $key -> " . $value);
        }
    }

    public static function escreverLogComGetQuery()
    {
        $queries = self::getQueryLog();
        $formattedQueries = self::formatQueryLog($queries);

        foreach ($formattedQueries as $key => $value) {
            Log::debug("Query: $key -> " . $value);
        }
    }
}
