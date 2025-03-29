<?php

namespace App\Scopes\Servico;

use App\Enums\LancamentoStatusTipoEnum;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Servico\ServicoPagamentoLancamento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoCanceladoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Pega o alias da tabela se existir, ou usa o nome original da tabela
        $tableAlias = $builder->getQuery()->from;

        if (strpos($tableAlias, ' as ') !== false) {
            // Pega o alias, se estiver presente
            [$tableName, $alias] = explode(' as ', $tableAlias);
            $tableAlias = trim($alias); // Aplica o alias

            // Seleciona a subconsulta para somar os valores liquidados
            $builder->selectSub(function ($query) use ($tableAlias) {
                $tableSubAlias = (new ServicoPagamentoLancamento())->getTableAsName();
                $query->from((new ServicoPagamentoLancamento())->getTableNameAsName())
                    ->selectRaw("COALESCE(SUM(ROUND(CAST({$tableSubAlias}.valor_esperado AS numeric), 2)), 0)")
                    ->whereNull("{$tableSubAlias}.deleted_at")
                    ->whereIn(
                        "{$tableSubAlias}.status_id",
                        LancamentoStatusTipoEnum::statusCanceladoScope(),
                    )
                    ->whereColumn("{$tableSubAlias}.pagamento_id", "{$tableAlias}.id")
                    ->where("{$tableSubAlias}.tenant_id", tenant('id'))
                    ->whereIn("{$tableSubAlias}.domain_id", TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
            }, 'total_cancelado');
        } else {
            $builder->withSum(['lancamentos as total_cancelado' => function ($query) {
                $query->whereIn(
                    'status_id',
                    LancamentoStatusTipoEnum::statusCanceladoScope()
                );
            }], DB::raw('ROUND(CAST(valor_esperado AS numeric), 2)'));
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoCancelado', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
