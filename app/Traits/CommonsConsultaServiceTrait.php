<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

trait CommonsConsultaServiceTrait
{

    public function postConsultaFiltros(Fluent $requestData)
    {
        $query = $this->consultaSimplesComFiltros($requestData);
        // RestResponse::createTestResponse([$query->toSql(),$query->getBindings()]);
        return $query->paginate($requestData->perPage ?? 25)->toArray();
    }

    public function consultaSimplesComFiltros(Fluent $requestData)
    {
        $filtros = $requestData->filtros ?? [];
        $arrayCamposFiltros = $this->traducaoCampos($filtros);

        $query = $this->model::query()
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->from($this->model::getTableNameAsName())
            ->select($this->model::getTableAsName() . '.*');

        $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($requestData->toArray());
        $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($requestData->toArray());

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

        $query->where($this->model::getTableAsName() . '.deleted_at', null);
        $this->verificaUsoScopeTenant($query, $this->model);
        $this->verificaUsoScopeDomain($query, $this->model);

        $query->when($requestData, function ($query) use ($requestData) {
            $ordenacao = $requestData->ordenacao ?? [];
            if (!count($ordenacao)) {
                $query->orderBy('nome', 'asc');
            } else {
                foreach ($ordenacao as $key => $value) {
                    $direcao =  isset($ordenacao[$key]['direcao']) && in_array($ordenacao[$key]['direcao'], ['asc', 'desc', 'ASC', 'DESC']) ? $ordenacao[$key]['direcao'] : 'asc';
                    $query->orderBy($ordenacao[$key]['campo'], $direcao);
                }
            }
        });

        return $query;
    }

    public function verificaUsoScopeTenant(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\Stancl\Tenancy\Database\Concerns\BelongsToTenant::class, class_uses_recursive($modelClass))) {
            $query->withoutTenancy();
            $query->where($modelClass::getTableAsName() . '.' . BelongsToTenant::$tenantIdColumn, tenant('id'));
        }
    }

    public function verificaUsoScopeDomain(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\App\Traits\BelongsToDomain::class, class_uses_recursive($modelClass))) {
            $query->withoutDomain();
            $query->where($modelClass::getTableAsName() . '.' . BelongsToDomain::$domainIdColumn, DomainTenantResolver::$currentDomain->id);
        }
    }
}
