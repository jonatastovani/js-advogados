<?php

namespace App\Services\GPU;

use App\Models\GPU\PessoaGPU;
use App\Common\RestResponse;
use App\Common\CommonsFunctions;
use App\Helpers\ValidationRecordsHelper;
use App\Traits\CommonServiceMethodsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class PessoaGPUService
{
    use CommonServiceMethodsTrait;

    public function __construct(public PessoaGPU $model)
    {
        $this->model = $model;
    }

    public function buscarRecursoPorId(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('pai', 'mae', 'perfis', 'rg', 'cpf', 'oab', 'documentosAtivos');
        return $resource;
    }

    public function buscarPorDocumento(Request $request)
    {
        $query = $this->model::with('pai', 'mae')->join('documentos.tb_documentos', 'pessoa.tb_pessoa.pess_id', '=', 'documentos.tb_documentos.docm_id_pessoa')
            ->join('comum.tb_tipo_documento', 'documentos.tb_documentos.docm_id_tipo_documento', '=', 'comum.tb_tipo_documento.tipd_id')
            ->where(function ($query) {
                $query->where('comum.tb_tipo_documento.tipd_id', 1)
                    ->orWhere('comum.tb_tipo_documento.tipd_id', 2);
            });

        $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($request->all());

        $query->where('documentos.tb_documentos.docm_nm_documento', $parametrosLike['conectivo'], $parametrosLike['curinga_inicio_caractere'] . $request->input('text') . $parametrosLike['curinga_final_caractere']);

        $result = $query->select('pessoa.tb_pessoa.*')->paginate($request->input('per_page', 50));

        return $result;
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - 'campos_busca' => ['col_nome'] (mapeado para 'pess_nome')
     * - 'campos_busca' => ['col_rg'] (mapeado para 'doc_rg.docm_nm_documento')
     * - 'campos_busca' => ['col_cpf'] (mapeado para 'doc_cpf.docm_nm_documento')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = isset($dados['aliasCampos']) ? $dados['aliasCampos'] : [];
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : 'pess',
            'col_rg' => isset($aliasCampos['col_rg']) ? $aliasCampos['col_rg'] : 'doc_rg',
            'col_cpf' => isset($aliasCampos['col_cpf']) ? $aliasCampos['col_cpf'] : 'doc_cpf',
            'col_oab' => isset($aliasCampos['col_oab']) ? $aliasCampos['col_oab'] : 'doc_oab',
            'col_nome_social' => isset($aliasCampos['col_nome_social']) ? $aliasCampos['col_nome_social'] : 'pesa_nome_social',
            'col_vulgo_alias' => isset($aliasCampos['col_vulgo_alias']) ? $aliasCampos['col_vulgo_alias'] : 'pesa_alias',
            'col_telefone' => isset($aliasCampos['col_telefone']) ? $aliasCampos['col_telefone'] : 'pess_end_tel',
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.pess_nome'],
            'col_rg' => [
                'campo' => $arrayAliasCampos['col_rg'] . '.docm_nm_documento',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_cpf' => [
                'campo' => $arrayAliasCampos['col_cpf'] . '.docm_nm_documento',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_oab' => [
                'campo' => $arrayAliasCampos['col_oab'] . '.docm_nm_documento',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_nome_social' => ['campo' => $arrayAliasCampos['col_nome_social'] . '.pesa_alias'],
            'col_vulgo_alias' => ['campo' => $arrayAliasCampos['col_vulgo_alias'] . '.pesa_alias'],
            'col_telefone' => [
                'campo' => $arrayAliasCampos['col_telefone'] . '.peen_nu_telefone',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function buscarRecurso(Fluent $requestData)
    {
        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['pess_id' => $requestData->id], !$withTrashed);

        if ($resource->count() == 0) {
            // Usa o método do trait para gerar o log e lançar a exceção
            return $this->gerarLogRecursoNaoEncontrado(
                404,
                "A Pessoa com ID {$requestData->id} não existe.",
                $requestData,
            );
        }

        // Retorna somente um registro
        return $resource[0];
    }
}
