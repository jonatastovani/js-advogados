<?php

namespace App\Services\GPU;

use App\Models\GPU\PessoaPaiMaeGPU;
use App\Common\RestResponse;
use App\Common\CommonsFunctions;
use Illuminate\Http\Request;

class PessoaPaiMaeGPUService
{
    protected $model;

    public function __construct(PessoaPaiMaeGPU $model)
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
                $mensagem = "O registro de Pai ou Mãe do PessoaGPU com ID $id não existe ou foi excluído.";
                $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | id: $id");

                $response = RestResponse::createErrorResponse($codigo, $mensagem, $traceId);
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
            return null;
        }

        return $resource;
    }

    /**
     * Recupera um recurso pelo ID Pessoa associado.
     *
     * @param int $id O ID do recurso.
     * @param array $options Opções adicionais.
     *                      - responseAuto (bool): Se deve gerar automaticamente uma resposta se o recurso não for encontrado. Padrão é false.
     *                      - blnAtual (bool): Se o(s) registro(s) a ser retornado(s) é(são) o(s) atual(is). Padrão é todos.
     *                      - tipo (string): Tipo ('P' para pai ou 'M' para mãe): Se o(s) registro(s) a ser retornado(s) é(são) o(s) pai(s) ou mãe(s) da(s) pessoa informada. Padrão é todos.
     *                      - orderBy (string): (Informar o nome do campo) Campo para ordenação. Padrão é id de registro (pame_id).
     *                      - direction (string): (ASC ou DESC) Direção da ordenação. Padrão é DESC.
     *                      - limit (int): Quantidade de registros a serem retornados. Padrão é todos.
     * @return mixed|null O recurso se encontrado, nulo se não for encontrado.
     * @throws \Exception Se o recurso não for encontrado e a opção responseAuto estiver definida como true.
     */
    public function buscarRecursoPorIdPessoa($id, $options = [])
    {
        $responseAuto = isset($options['responseAuto']) ? $options['responseAuto'] : false;
        $resource = $this->model::where('pame_id_pessoa', $id)
            // Insere para ser retornado(s) o(s) atual(is), caso seja informado o campo blnAtual como true.
            ->when((isset($options['blnAtual']) && in_array($options['blnAtual'], [true, 1])), function ($query) {
                $query->where('pame_fl_atual', 1);
            })
            // Insere o tipo de pessoa (P ou M) se caso seja informado.
            ->when((isset($options['tipo']) && in_array($options['tipo'], ['P', 'M'])), function ($query) use ($options) {
                $query->where('pame_fl_tipo', $options['tipo']);
            })
            // Insere a ordenação e direção da consulta.
            ->when($options, function ($query) use ($options) {
                $orderBy = isset($options['orderBy']) ? $options['orderBy'] : 'pame_id';
                if (isset($options['direction']) && in_array($options['orderBy'], ['ASC', 'DESC', 'asc', 'desc'])) {
                    $query->orderBy($orderBy, $options['direction']);
                } else {
                    $query->orderByDesc($orderBy);
                }
            })->when((isset($options['limit']) && is_numeric($options['limit'])), function ($query) use ($options) {
                $query->limit($options['limit']);
            })->get();

        if (!$resource) {
            if ($responseAuto) {
                $codigo = 404;
                $mensagem = "O registro de Pai ou Mãe do PessoaGPU com ID $id não existe ou foi excluído.";
                $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | id: $id");

                $response = RestResponse::createErrorResponse($codigo, $mensagem, $traceId);
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
            return null;
        }

        return $resource;
    }

}
