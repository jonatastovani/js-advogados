<?php

namespace App\Services\Comum;

use App\Common\RestResponse;
use App\Services\Service;
use App\Helpers\CurlRequest;
use Illuminate\Support\Fluent;

class CepService extends Service
{
    private string $baseUrl = 'https://brasilapi.com.br/api/cep/v1/';

    protected function traducaoCampos(array $dados)
    {
        return [];
    }

    /**
     * Busca um CEP utilizando a BrasilAPI via CurlRequest
     *
     * @param Fluent $requestData
     * @return array
     */
    public function show(Fluent $requestData): array
    {
        $cep = preg_replace('/\D/', '', $requestData->cep); // Remove caracteres não numéricos

        if (strlen($cep) !== 8) {
            return ['error' => 'CEP inválido. Informe um CEP com 8 dígitos.'];
        }

        try {
            // Instancia a requisição CURL
            $curl = new CurlRequest("{$this->baseUrl}{$cep}", 'GET');

            // Executa a requisição
            $result = $curl->execute();

            // Verifica se houve erro ou se a resposta está vazia
            if (!isset($result['response']) || empty($result['response'])) {
                RestResponse::createGenericResponse(['errors' => ['CEP não encontrado ou API indisponível.']], 422, 'Falha na busca de CEP.');
            }

            // Decodifica a resposta JSON
            $responseData = json_decode($result['response'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                RestResponse::createGenericResponse(['errors' => ['Erro ao processar resposta da API.']], 422, 'Falha na busca de CEP.');
            }

            return $responseData;
        } catch (\Exception $e) {
            return RestResponse::createGenericResponse(['errors' => ['Erro ao consultar CEP. Detalhes: ' . $e->getMessage()]], 422, 'Falha na busca de CEP.');
        }
    }
}
