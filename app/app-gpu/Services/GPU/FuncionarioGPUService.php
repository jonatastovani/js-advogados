<?php

namespace App\Services\GPU;

use App\Common\RestResponse;
use App\Common\CommonsFunctions;
use App\Models\GPU\FuncionarioGPU;
use App\Traits\CommonServiceMethodsTrait;

class FuncionarioGPUService
{
    use CommonServiceMethodsTrait;

    protected $model;

    public function __construct(FuncionarioGPU $model)
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
                $mensagem = "O Funcionário com ID $id não existe.";
                $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | id: $id");

                $response = RestResponse::createErrorResponse($codigo, $mensagem, $traceId);
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
            return null;
        } else {
            $resource->load('servidorPessoaGepen');
        }

        return $resource;
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'aliasTabela' (alias da tabela que contém os campos que serão traduzidos) por padrão 'func'.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - 'campos_busca' => ['col_nome'] (mapeado para '{func}pess_nome')
     * - 'campos_busca' => ['col_rg'] (mapeado para '{func}.docm_nm_documento')
     * - 'campos_busca' => ['col_cpf'] (mapeado para '{func}.docm_nm_documento')
     * - 'campos_busca' => ['col_rs'] (mapeado para '{func}.docm_nm_documento')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasTabela = isset($dados['aliasTabela']) ? $dados['aliasTabela'] : ($this->model->getTableAsName() ?? 'func');
        $arrayCampos = [
            'col_nome' => ['campo' => "$aliasTabela.nome"],
            'col_rg' => [
                'campo' => "$aliasTabela.rg",
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_cpf' => [
                'campo' => "$aliasTabela.cpf",
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_rs' => [
                'campo' => "$aliasTabela.rs",
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }
}
