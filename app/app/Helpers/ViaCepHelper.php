<?php

namespace App\Helpers;

use Exception;

class ViaCepHelper
{
    private const BASE_URL = 'https://viacep.com.br/ws';

    /**
     * Busca endereço pelo CEP.
     *
     * @param string $cep
     * @return array
     * @throws Exception
     */
    public static function buscarPorCep(string $cep): array
    {
        try {
            // Valida o formato do CEP
            if (!preg_match('/^\d{8}$/', $cep)) {
                throw new Exception('CEP inválido. Use o formato: 12345678.', 400);
            }

            $url = self::BASE_URL . "/{$cep}/json/";

            $curlRequest = new CurlRequest($url, 'GET');

            $response = $curlRequest->execute();

            $data = json_decode($response['response'], true);

            if (isset($data['erro']) && $data['erro'] === true) {
                throw new RequestException('CEP não encontrado.', 404);
            }

            return $data;
        } catch (RequestException $e) {
            throw $e; // Exceção já formatada com código HTTP correto
        } catch (Exception $e) {
            
            throw new Exception('Erro ao buscar endereço. Verifique sua conexão com a internet.', 503);
        }
    }

    /**
     * Busca endereços por UF, cidade e logradouro.
     *
     * @param string $uf
     * @param string $cidade
     * @param string $logradouro
     * @return array
     * @throws Exception
     */
    public static function buscarPorEndereco(string $uf, string $cidade, string $logradouro): array
    {
        try {
            // Valida parâmetros básicos
            if (strlen($uf) !== 2) {
                throw new RequestException('UF inválido. Use o formato: SP.', 400);
            }

            if (empty($cidade) || empty($logradouro)) {
                throw new RequestException('Cidade e logradouro são obrigatórios.', 400);
            }

            $url = self::BASE_URL . "/{$uf}/" . urlencode($cidade) . "/" . urlencode($logradouro) . "/json/";

            $curlRequest = new CurlRequest($url, 'GET');

            $response = $curlRequest->execute();

            $data = json_decode($response['response'], true);

            if (empty($data)) {
                throw new RequestException('Endereço não encontrado.', 404);
            }

            return $data;
        } catch (RequestException $e) {
            throw $e; // Exceção já formatada com código HTTP correto
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar endereço. Verifique sua conexão com a internet.', 503);
        }
    }
}
