<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use InvalidArgumentException;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

trait CommonsConsultaServiceTrait
{

    // public function postConsultaFiltros(Fluent $requestData, array $options = [])
    // {
    //     $query = $this->consultaSimplesComFiltros($requestData);

    //     // Verifica se o método 'loadFull' existe antes de chamar
    //     if (method_exists($this, 'loadFull')) {
    //         $query->with($this->loadFull());
    //     }

    //     return $query->paginate($requestData->perPage ?? 25)->toArray();
    // }

    /**
     * Realiza a consulta com base nos filtros fornecidos e retorna os resultados paginados.
     *
     * @param Fluent $requestData Dados da requisição contendo filtros, ordenações e paginação.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return array Resultado paginado da consulta.
     */
    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        return $this->consultaSimplesComFiltros($requestData);
    }

    public function consultaSimplesComFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosTexto($filtrosData['query'], $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, $options);
        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    // public function consultaSimplesComFiltros(Fluent $requestData, array $options = [])
    // {
    //     $filtros = $requestData->filtros ?? [];
    //     $arrayCamposFiltros = $this->traducaoCampos($filtros);
    //     $arrayCamposSelect = $options['arrayCamposSelect'] ?? ['*'];

    //     $strSelect = '';
    //     foreach ($arrayCamposSelect as $value) {
    //         if ($strSelect != '') {
    //             $strSelect .= ', ';
    //         }
    //         $strSelect .= "{$this->model->getTableAsName()}.$value";
    //     }
    //     // RestResponse::createTestResponse([$strSelect, $arrayCamposSelect]);
    //     $query = $this->model::query()
    //         ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
    //         ->from($this->model->getTableNameAsName())
    //         ->select($strSelect);

    //     $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($requestData->toArray());
    //     $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($requestData->toArray());

    //     if (count($arrayTexto) && $arrayTexto[0] != '') {
    //         $query->where(function ($subQuery) use ($arrayTexto, $arrayCamposFiltros, $parametrosLike) {
    //             foreach ($arrayTexto as $texto) {
    //                 foreach ($arrayCamposFiltros as $campo) {
    //                     if (isset($campo['tratamento'])) {
    //                         $trait = $this->tratamentoDeTextoPorTipoDeCampo($texto, $campo);
    //                         $texto = $trait['texto'];
    //                         $campoNome = DB::raw($trait['campo']);
    //                     } else {
    //                         $campoNome = DB::raw("CAST({$campo['campo']} AS TEXT)");
    //                     }
    //                     $subQuery->orWhere($campoNome, $parametrosLike['conectivo'], $parametrosLike['curinga_inicio_caractere'] . $texto . $parametrosLike['curinga_final_caractere']);
    //                 }
    //             }
    //         });
    //     }

    //     $query->where($this->model->getTableAsName() . '.deleted_at', null);
    //     $this->verificaUsoScopeTenant($query, $this->model);
    //     $this->verificaUsoScopeDomain($query, $this->model);

    //     $query->when($requestData, function ($query) use ($requestData) {
    //         $ordenacao = $requestData->ordenacao ?? [];
    //         if (!count($ordenacao)) {
    //             $query->orderBy('nome', 'asc');
    //         } else {
    //             foreach ($ordenacao as $key => $value) {
    //                 $direcao =  isset($ordenacao[$key]['direcao']) && in_array($ordenacao[$key]['direcao'], ['asc', 'desc', 'ASC', 'DESC']) ? $ordenacao[$key]['direcao'] : 'asc';
    //                 $query->orderBy($ordenacao[$key]['campo'], $direcao);
    //             }
    //         }
    //     });

    //     return $query;
    // }

    protected function verificaUsoScopeTenant(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\Stancl\Tenancy\Database\Concerns\BelongsToTenant::class, class_uses_recursive($modelClass))) {
            $query->withoutTenancy();
            $query->where($modelClass->getTableAsName() . '.' . BelongsToTenant::$tenantIdColumn, tenant('id'));
        }
    }

    protected function verificaUsoScopeDomain(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\App\Traits\BelongsToDomain::class, class_uses_recursive($modelClass))) {
            $query->withoutDomain();
            $query->where($modelClass->getTableAsName() . '.' . BelongsToDomain::$domainIdColumn, DomainTenantResolver::$currentDomain->id);
        }
    }

    /**
     * Extrai os filtros e inicializa o query builder para a consulta.
     *
     * @param Fluent $requestData Dados da requisição contendo os filtros.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return array Retorna um array com a query inicializada e os parâmetros processados:
     *               - query: Builder da consulta inicial
     *               - filtros: Filtros aplicados na consulta
     *               - arrayCamposFiltros: Campos traduzidos para uso no filtro
     *               - arrayTexto: Textos extraídos para busca textual
     *               - parametrosLike: Parâmetros de busca usando LIKE
     */
    protected function extrairFiltros(Fluent $requestData, array $options = [])
    {
        $filtros = $requestData->filtros ?? [];
        $arrayCamposFiltros = $this->traducaoCampos($filtros);
        $arrayCamposSelect = $options['arrayCamposSelect'] ?? ['*'];

        $strSelect = '';
        foreach ($arrayCamposSelect as $value) {
            if ($strSelect != '') {
                $strSelect .= ', ';
            }
            $strSelect .= "{$this->model->getTableAsName()}.$value";
        }

        $query = $this->model::query()
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->from($this->model->getTableNameAsName())
            ->select($strSelect);

        $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($requestData->toArray());
        $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($requestData->toArray());

        return compact('query', 'filtros', 'arrayCamposFiltros', 'arrayTexto', 'parametrosLike');
    }

    /**
     * Aplica filtros textuais usando os campos e valores fornecidos.
     *
     * @param Builder $query Instância do query builder.
     * @param array $arrayTexto Textos extraídos para a busca.
     * @param array $arrayCamposFiltros Campos de filtros traduzidos.
     * @param array $parametrosLike Parâmetros configurados para buscas usando LIKE.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query modificada com os filtros textuais aplicados.
     */
    protected function aplicarFiltrosTexto(Builder $query, array $arrayTexto, array $arrayCamposFiltros, array $parametrosLike, array $options = [])
    {
        if (count($arrayTexto) && $arrayTexto[0] != '') {
            $query->where(function ($subQuery) use ($arrayTexto, $arrayCamposFiltros, $parametrosLike) {
                foreach ($arrayTexto as $texto) {
                    foreach ($arrayCamposFiltros as $campo) {
                        if (isset($campo['tratamento'])) {
                            $trait = $this->tratamentoDeTextoPorTipoDeCampo($texto, $campo);
                            $texto = $trait['texto'];
                            $campoNome = DB::raw($trait['campo']);
                        } else {
                            $campoNome = DB::raw("CAST({$campo['campo']} AS TEXT)");
                        }
                        $subQuery->orWhere($campoNome, $parametrosLike['conectivo'], $parametrosLike['curinga_inicio_caractere'] . $texto . $parametrosLike['curinga_final_caractere']);
                    }
                }
            });
        }

        return $query;
    }

    /**
     * Aplica scopes padrões em uma query builder.
     * 
     * - deleted_at IS NULL
     * - Verifica se a trait BelongsToTenant está sendo utilizada e adiciona o scope
     * - Verifica se a trait BelongsToDomain está sendo utilizada e adiciona o scope
     *
     * @param Builder $query Instância do query builder.
     * @param Model $model Model que vai ser verificado para saber se tem os scopes.
     * @param array $options Parâmetros adicionais.
     * @return Builder Retorna a query modificada com os scopes padrões aplicados.
     */
    protected function aplicarScopesPadrao(Builder $query, Model $model = null, array $options = [])
    {
        $modelVerificar = is_null($model) ? $this->model : $model;

        $query->where($modelVerificar->getTableAsName() . '.deleted_at', null);
        $this->verificaUsoScopeTenant($query, $modelVerificar);
        $this->verificaUsoScopeDomain($query, $modelVerificar);

        return $query;
    }

    /**
     * Aplica ordenações e validações adicionais na query.
     *
     * @param Builder $query Instância do query builder.
     * @param Fluent $requestData Dados da requisição contendo as informações de ordenação.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query com as ordenações aplicadas.
     */
    protected function aplicarOrdenacoes(Builder $query, Fluent $requestData, array $options = [])
    {
        $campoOrdenacao = $options['campoOrdenacao'] ?? 'nome';
        $direcaoOrdenacao = $options['direcaoOrdenacao'] ?? 'asc';

        $query->when($requestData, function ($query) use ($requestData, $campoOrdenacao, $direcaoOrdenacao) {
            $ordenacao = $requestData->ordenacao ?? [];
            if (!count($ordenacao)) {
                $query->orderBy($campoOrdenacao, $direcaoOrdenacao);
            } else {
                foreach ($ordenacao as $key => $value) {
                    $direcao =  isset($ordenacao[$key]['direcao']) && in_array($ordenacao[$key]['direcao'], ['asc', 'desc', 'ASC', 'DESC']) ? $ordenacao[$key]['direcao'] : 'asc';
                    $query->orderBy($ordenacao[$key]['campo'], $direcao);
                }
            }
        });

        return $query;
    }

    /**
     * Carrega os relacionamentos definidos e retorna os resultados paginados.
     *
     * @param Builder $query Instância do query builder.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @param Fluent $requestData Dados da requisição contendo as informações de paginação.
     * @return array Retorna o resultado paginado da consulta.
     */
    protected function carregarRelacionamentos(Builder $query, Fluent $requestData, array $options = [])
    {
        if ($options['loadFull'] ?? false) {
            $query->with($options['loadFull']);
        } else {
            if (method_exists($this, 'loadFull') && is_array($this->loadFull())) {
                $query->with($this->loadFull($options));
            }
        }
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($requestData->perPage ?? 25);

        return $paginator->toArray();
    }

    /**
     * Aplica um filtro de intervalo de datas em uma query.
     *
     * Este método filtra os resultados de uma query com base em um intervalo de datas
     * especificado no parâmetro `$requestData`. O filtro é aplicado ao campo especificado
     * em `datas_intervalo['campo_data']`, considerando as datas de início e fim fornecidas.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     *     A query na qual o filtro será aplicado.
     * @param \Illuminate\Support\Fluent $requestData
     *     Os dados da requisição contendo as informações de filtro.
     * @param array $options
     *     Opções adicionais para personalizar o comportamento do filtro (não utilizado atualmente).
     * 
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *     A query modificada com o filtro de intervalo de datas aplicado.
     *
     * @throws \RestResponse
     *     Se as informações de `campo_data`, `data_inicio` ou `data_fim` não estiverem presentes no `$requestData`.
     */
    protected function aplicarFiltroDataIntervalo(Builder $query, Fluent $requestData, array $options = [])
    {
        // Valida se os campos necessários estão presentes no requestData
        if (
            empty($requestData->datas_intervalo['campo_data']) ||
            !is_string($requestData->datas_intervalo['campo_data']) ||
            empty($requestData->datas_intervalo['data_inicio']) ||
            !strtotime($requestData->datas_intervalo['data_inicio']) ||
            empty($requestData->datas_intervalo['data_fim']) ||
            !strtotime($requestData->datas_intervalo['data_fim'])
        ) {
            $log = LogHelper::gerarLogDinamico(400, 'As informações de intervalo de datas (campo_data, data_inicio, data_fim) são obrigatórias e devem ser válidas.', $requestData);
            RestResponse::createErrorResponse(400, $log->error, $log->trace_id)->throwResponse();
        }

        // Recupera os valores do intervalo de datas
        $campoData = $requestData->datas_intervalo['campo_data'];
        $dataInicio = $requestData->datas_intervalo['data_inicio'];
        $dataFim = $requestData->datas_intervalo['data_fim'];

        // Aplica o filtro de intervalo de datas
        return $query->whereBetween($campoData, [$dataInicio, $dataFim]);
    }

    /**
     * Aplica um filtro de data (ano e mês) em uma query.
     *
     * Este método filtra os resultados de uma query com base em uma data especificada no formato `YYYY-MM`.
     * O campo a ser filtrado é indicado no parâmetro `$campoData`.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     *     A query na qual o filtro será aplicado.
     * @param \Illuminate\Support\Fluent $requestData
     *     Os dados da requisição contendo as informações de filtro.
     * @param string $campoData
     *     O nome do campo na base de dados que será usado para o filtro (exemplo: `created_at`).
     * @param array $options
     *     Opções adicionais para personalizar o comportamento do filtro (não utilizado atualmente).
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *     A query modificada com o filtro de data (ano e mês) aplicado.
     *
     * @throws \RestResponse
     *     Se as informações de `mes_ano` não forem enviadas ou estiverem inválidas.
     * @throws \InvalidArgumentException
     *     Se o campo `$campoData` não for válido.
     */
    protected function aplicarFiltroMes(Builder $query, Fluent $requestData, string $campoData, array $options = [])
    {
        // Valida o campo de data a ser filtrado
        if (empty($campoData) || !is_string($campoData)) {
            throw new InvalidArgumentException('O campo de data a ser filtrado ($campoData) é obrigatório e deve ser uma string válida.');
        }

        // Valida os campos obrigatórios
        if (
            empty($requestData->mes_ano) ||
            !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requestData->mes_ano) // Valida formato YYYY-MM
        ) {
            // Gera o log e lança uma resposta de erro padronizada
            $log = LogHelper::gerarLogDinamico(
                400,
                'As informações de filtro de mês (mes_ano) são obrigatórias e devem estar no formato YYYY-MM.',
                $requestData
            );
            RestResponse::createErrorResponse(400, $log->error, $log->trace_id)->throwResponse();
        }

        // Recupera o valor do mês
        $mesAno = $requestData->mes_ano;

        // Extrai o ano e o mês da data
        [$ano, $mes] = explode('-', $mesAno);

        // Aplica o filtro de ano e mês na query
        return $query->whereYear($campoData, $ano)->whereMonth($campoData, $mes);
    }
}
