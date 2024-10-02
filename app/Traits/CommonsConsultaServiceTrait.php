<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait CommonsConsultaServiceTrait
{
    public function consultaSimplesComFiltros(Request $request)
    {
        $filtros = $request->has('filtros') ? $request->input('filtros') : [];
        $arrayCamposFiltros = $this->traducaoCampos($filtros);

        $query = $this->model::query()
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->from($this->model::getTableNameAsName())
            ->select($this->model::getTableAsName() . '.*');

        $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($request->all());
        $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($request->all());

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

        $query->when($request, function ($query) use ($request) {
            $ordenacao = $request->has('ordenacao') ? $request->input('ordenacao') : [];
            if (!count($ordenacao)) {
                $query->orderBy('nome', 'asc');
            } else {
                foreach ($ordenacao as $key => $value) {
                    $direcao =  isset($ordenacao[$key]['direcao']) && in_array($ordenacao[$key]['direcao'], ['asc, desc, ASC, DESC']) ? $ordenacao[$key]['direcao'] : 'asc';
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
            $query->where($modelClass::getTableAsName() . '.tenant_id', tenant('id'));
        }
    }
}
