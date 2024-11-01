<?php

namespace App\Services\Foto;

use App\Common\CommonsFunctions;
use App\Enums\SubTipoFotoEnum;
use App\Helpers\CurlRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class FotoManagerGPUService
{
    protected $servidor;
    protected $token;

    /**
     * Construtor da classe FotoManagerGPUService.
     * Inicializa as configurações do serviço, como o servidor e o token de autenticação.
     *
     * @param array $dados (Opcional) Array de dados que pode conter o token personalizado para autenticação.
     */
    public function __construct(array $dados = [])
    {
        $this->servidor = env('SERVIDOR_FOTO_URL_SERVICO');

        if (isset($dados['token'])) {
            $this->token = $dados['token'];
        } else {
            $this->token = $this->getTokenDefault();
        }
    }

    /**
     * Busca uma lista de fotos com base no ID, tipo e subtipo fornecidos.
     *
     * @param int $id O ID do objeto para buscar as fotos.
     * @param string $tipo O tipo de objeto (ex: PRESO, FUNCIONARIO).
     * @param string|null $subTipo (Opcional) O subtipo da foto.
     * @return array O array contendo as fotos ou detalhes do erro.
     */
    public function getFotos(int $id, string $tipo, ?string $subTipo = null)
    {
        try {
            $url = $this->servidor . '/v1/getfotos';
            $queryParams = [
                'id' => $id,
                'tipo' => $tipo,
                'subTipo' => $subTipo ?? SubTipoFotoEnum::FRONTAL,
            ];

            $curl = new CurlRequest($url . '?' . http_build_query($queryParams), 'GET');
            $curl->setHeader('Authorization', $this->token);
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Accept', 'application/json');
            $response = $curl->execute();

            $responseFluent = new Fluent($response);
            if ($responseFluent->info['http_code'] == 200) {
                $successFluent = new Fluent();
                $successFluent->codigo = 200;
                $successFluent->response = json_decode($responseFluent->response, true);
                return $successFluent->toArray();
            } else {
                $errorFluent = new Fluent();
                $errorFluent->codigo = $responseFluent->info['http_code'];
                $errorFluent->mensagem = "Erro ao buscar fotos: HTTP {$responseFluent->info['http_code']}.";
                $errorFluent->traceId = CommonsFunctions::generateLog("$errorFluent->codigo | $errorFluent->mensagem | id: $id | tipo: $tipo | subTipo: $subTipo");
                return $errorFluent->toArray();
            }
        } catch (Exception $e) {
            $errorFluent = new Fluent();
            $errorFluent->codigo = 500;
            $errorFluent->mensagem = $e->getMessage();
            $errorFluent->traceId = CommonsFunctions::generateLog("$errorFluent->codigo | $errorFluent->mensagem | id: $id | tipo: $tipo | subTipo: $subTipo");
            return $errorFluent->toArray();
        }
    }

    /**
     * Busca o caminho de uma foto específica com base no ID, tipo e subtipo fornecidos.
     *
     * @param int $id O ID do objeto para buscar o caminho da foto.
     * @param string $tipo O tipo de objeto (ex: PRESO, FUNCIONARIO).
     * @param string|null $subTipo (Opcional) O subtipo da foto.
     * @return mixed O caminho da foto ou null em caso de erro.
     */
    public function getCaminhoFoto(int $id, string $tipo, ?string $subTipo = null)
    {
        try {
            $url = $this->servidor . '/v1/getCaminhoFoto';
            $queryParams = [
                'id' => $id,
                'tipo' => $tipo,
                'subTipo' => $subTipo ?? SubTipoFotoEnum::FRONTAL,
            ];

            $curl = new CurlRequest($url . '?' . http_build_query($queryParams), 'GET');
            $curl->setHeader('Authorization', $this->token);
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Accept', 'application/json');
            $response = $curl->execute();

            if ($response['info']['http_code'] == 200) {
                return json_decode($response['response']);
            } else {
                Log::error("Erro ao buscar caminho da foto: HTTP " . $response['info']['http_code']);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Erro ao buscar caminho da foto: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca uma única foto com base no ID, tipo e subtipo fornecidos.
     *
     * @param int $id O ID do objeto para buscar a foto.
     * @param string $tipo O tipo de objeto (ex: PRESO, FUNCIONARIO).
     * @param string|null $subTipo (Opcional) O subtipo da foto.
     * @return array O array contendo a foto ou detalhes do erro.
     */
    public function getFoto(int $id, string $tipo, ?string $subTipo = null)
    {
        try {
            $url = $this->servidor . '/v1/getfoto';
            $queryParams = [
                'id' => $id,
                'tipo' => $tipo,
                'subTipo' => $subTipo ?? SubTipoFotoEnum::FRONTAL,
            ];

            $curl = new CurlRequest($url . '?' . http_build_query($queryParams), 'GET');
            $curl->setHeader('Authorization', $this->token);
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Accept', 'application/json');
            $response = $curl->execute();

            $responseFluent = new Fluent($response);
            if ($responseFluent->info['http_code'] == 200) {
                $successFluent = new Fluent();
                $successFluent->code = 200;
                $successFluent->response = json_decode($responseFluent->response, true);
                return $successFluent->toArray();
            } else {
                $errorFluent = new Fluent();
                $errorFluent->code = $responseFluent->info['http_code'];
                $errorFluent->message = "Erro ao buscar foto: HTTP {$responseFluent->info['http_code']}.";
                $errorFluent->traceId = CommonsFunctions::generateLog("$errorFluent->code | $errorFluent->message | id: $id | tipo: $tipo | subTipo: $subTipo");
                return $errorFluent->toArray();
            }
        } catch (Exception $e) {
            $errorFluent = new Fluent();
            $errorFluent->code = 500;
            $errorFluent->message = $e->getMessage();
            $errorFluent->traceId = CommonsFunctions::generateLog("$errorFluent->code | $errorFluent->message | id: $id | tipo: $tipo | subTipo: $subTipo");
            return $errorFluent->toArray();
        }
    }

    /**
     * Busca uma foto por nome com base no ID, tipo, nome e subtipo fornecidos.
     *
     * @param int $id O ID do objeto para buscar a foto.
     * @param string $tipo O tipo de objeto (ex: PRESO, FUNCIONARIO).
     * @param string $nome O nome da foto a ser buscada.
     * @param string|null $subTipo (Opcional) O subtipo da foto.
     * @return mixed A foto encontrada ou null em caso de erro.
     */
    public function getFotoPorNome(int $id, string $tipo, string $nome, ?string $subTipo = null)
    {
        try {
            $url = $this->servidor . '/v1/getFotoPorNome';
            $queryParams = [
                'id' => $id,
                'tipo' => $tipo,
                'nome' => $nome,
            ];
            if ($subTipo) {
                $queryParams['subTipo'] = $subTipo;
            }

            $curl = new CurlRequest($url . '?' . http_build_query($queryParams), 'GET');
            $curl->setHeader('Authorization', $this->token);
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Accept', 'application/json');
            $response = $curl->execute();

            if ($response['info']['http_code'] == 200) {
                return json_decode($response['response']);
            } else {
                Log::error("Erro ao buscar foto por nome: HTTP " . $response['info']['http_code']);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Erro ao buscar foto por nome: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtém o token padrão para autenticação nas requisições de fotos.
     *
     * @return string O token padrão configurado no arquivo .env.
     */
    private function getTokenDefault(): string
    {
        return env('SERVIDOR_FOTO_TOKEN');
    }
}
