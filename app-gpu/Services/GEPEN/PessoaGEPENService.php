<?php

namespace App\Services\GEPEN;

use App\Models\GEPEN\PessoaGEPEN;
use App\Common\RestResponse;
use App\Common\CommonsFunctions;

class PessoaGEPENService
{
    protected $model;

    public function __construct(PessoaGEPEN $model)
    {
        $this->model = $model;
    }

    /**
     * Recupera um recurso pelo seu ID.
     *
     * @param int $id O ID do recurso.
     * @param array $options Opções adicionais.
     *                      - responseAuto (bool): Se deve gerar automaticamente uma resposta se o recurso não for encontrado. Padrão é false.
     * @return mixed|null O recurso se encontrado, nulo se não for encontrado.
     * @throws \Exception Se o recurso não for encontrado e a opção responseAuto estiver definida como true.
     */
    public function buscarRecursoPorId($id, array $options = [])
    {
        $resource = $this->model::find($id);
        $responseAuto = isset($options['responseAuto']) ? $options['responseAuto'] : false;

        if (!$resource) {
            if ($responseAuto) {
                $codigo = 404;
                $mensagem = "A Pessoa com ID $id não existe ou foi excluída.";
                $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | id: $id");

                $response = RestResponse::createErrorResponse($codigo, $mensagem, $traceId);
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
            return null;
        }

        return $resource;
    }
}
