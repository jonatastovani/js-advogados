<?php

namespace App\Traits;

use Illuminate\Support\Fluent;
use App\Helpers\LogHelper;
use App\Common\RestResponse;

trait ServiceLogTrait
{
    // Variável para controlar o log globalmente
    protected $gerarLogDefault = true;

    /**
     * Gera o log e lança a exceção de recurso não encontrado.
     *
     * @param int $codigo Código do erro (ex: 404)
     * @param string $mensagem Mensagem de erro
     * @param Fluent $requestData Dados da requisição
     * @param array|null $options Parâmetros adicionais, ex: ['gerar_log' => false]
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function gerarLogRecursoNaoEncontrado(int $codigo, string $mensagem, Fluent $requestData, array $options = null)
    {
        // Verifica se a opção 'gerar_log' foi passada, caso contrário usa o valor padrão
        $gerarLog = $options['gerar_log'] ?? $this->gerarLogDefault;
        $retornarErro = isset($requestData->retornarErro) && $requestData->retornarErro == true ? true : false;

        if ($gerarLog) {
            // Gera o log se estiver habilitado
            $arrayErrors = LogHelper::gerarLogDinamico($codigo, $mensagem, $requestData);
            $response = RestResponse::createErrorResponse($codigo, $arrayErrors['error'], $arrayErrors['trace_id']);
        } else {
            // Apenas gera a resposta de erro sem o log
            $response = RestResponse::createErrorResponse($codigo, $mensagem);
        }

        if ($retornarErro) {
            return $response;
        }
        
        // Lança a exceção de erro
        return $response->throwResponse();
    }
}
