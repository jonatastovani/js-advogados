<?php

namespace App\Services\GPU;

use App\Models\GPU\PresoSincronizacaoGPU;
use App\Common\RestResponse;
use App\Common\CommonsFunctions;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Traits\CommonServiceMethodsTrait;
use Illuminate\Support\Fluent;

class PresoSincronizacaoGPUService
{
    use CommonServiceMethodsTrait;

    public function __construct(public PresoSincronizacaoGPU $model) {}

    public function buscarRecursoPorId(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        return $resource;
    }

    /**
     * Recupera recursos com base em filtros.
     *
     * @param array $dados Um array de dados contendo os filtros.
     *                       O campos $dados['texto'] é obrigatório e contém o texto de busca.
     * @return \Illuminate\Pagination\LengthAwarePaginator The retrieved resource.
     */
    public function buscarRecursoComFiltros(array $dados = [])
    {
        $resource = $this->model::where(function ($query) use ($dados) {
            // Pega somente os campos de retorno padrão, caso não seja informado nenhuma opção.
            $campos = self::traducaoCampos($dados);
            $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($dados);
            $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($dados);
            foreach ($arrayTexto as $texto) {
                foreach ($campos as $campo) {
                    $query->orWhere($campo, $parametrosLike['conectivo'], $parametrosLike['curinga_inicio_caractere'] . $texto . $parametrosLike['curinga_final_caractere']);
                }
            }
        })
            // Insere a ordenação e direção da consulta.
            ->when($dados, function ($query) use ($dados) {
                $orderBy = isset($dados['orderBy']) ? $dados['orderBy'] : 'psi_matricula';
                if (isset($dados['direction']) && in_array($dados['orderBy'], ['ASC', 'DESC', 'asc', 'desc'])) {
                    $query->orderBy($orderBy, $dados['direction']);
                } else {
                    $query->orderByDesc($orderBy);
                }
            })
            ->paginate(request('perPage', 25));

        return $resource;
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - 'campos_busca' => ['col_matricula'] (mapeado para 'psi_matricula')
     * - 'campos_busca' => ['col_nome'] (mapeado para 'psi_nome')
     * - 'campos_busca' => ['col_rg'] (mapeado para 'psi_cd_rg')
     * - 'campos_busca' => ['col_rg_criminal'] (mapeado para 'psi_cd_rg_criminal')
     * - 'campos_busca' => ['col_nome_social'] (mapeado para 'psi_pre_nome_social')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $arrayCampos = [
            'col_matricula' => [
                'campo' => 'psi_matricula',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_nome' => ['campo' => 'psi_nome'],
            'col_rg' => [
                'campo' => 'psi_cd_rg',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_rg_criminal' => [
                'campo' => 'psi_cd_rg_criminal',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_cpf' => [
                'campo' => 'psi_cd_cic',
                'tratamento' => [
                    'personalizado' => 'matricula_e_documento'
                ]
            ],
            'col_nome_social' => ['campo' => 'psi_pre_nome_social'],
            'col_vulgo_alias' => ['campo' => 'psvg.psvg_vulgo'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_matricula'], $dados);
    }

    public function buscarRecurso(Fluent $requestData)
    {
        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['psi_id_preso' => $requestData->id], !$withTrashed);

        if ($resource->count() == 0) {
            // Usa o método do trait para gerar o log e lançar a exceção
            return $this->gerarLogRecursoNaoEncontrado(
                404,
                "O Preso com ID {$requestData->id} não existe.",
                $requestData,
            );
        }

        // Retorna somente um registro
        return $resource[0];
    }
}
