<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
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
    //         $strSelect .= "{$this->model::getTableAsName()}.$value";
    //     }
    //     // RestResponse::createTestResponse([$strSelect, $arrayCamposSelect]);
    //     $query = $this->model::query()
    //         ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
    //         ->from($this->model::getTableNameAsName())
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

    //     $query->where($this->model::getTableAsName() . '.deleted_at', null);
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
            $query->where($modelClass::getTableAsName() . '.' . BelongsToTenant::$tenantIdColumn, tenant('id'));
        }
    }

    protected function verificaUsoScopeDomain(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\App\Traits\BelongsToDomain::class, class_uses_recursive($modelClass))) {
            $query->withoutDomain();
            $query->where($modelClass::getTableAsName() . '.' . BelongsToDomain::$domainIdColumn, DomainTenantResolver::$currentDomain->id);
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
            $strSelect .= "{$this->model::getTableAsName()}.$value";
        }
        // RestResponse::createTestResponse([$strSelect, $arrayCamposSelect]);
        $query = $this->model::query()
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->from($this->model::getTableNameAsName())
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

        return $query->groupBy($this->model::getTableAsName() . '.id');
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
        $query->where($this->model::getTableAsName() . '.deleted_at', null);
        $this->verificaUsoScopeTenant($query, $this->model);
        $this->verificaUsoScopeDomain($query, $this->model);

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
            if (method_exists($this, 'loadFull')) {
                $query->with($this->loadFull());
            }
        }
        return $query->paginate($requestData->perPage ?? 25)->toArray();
    }
}
