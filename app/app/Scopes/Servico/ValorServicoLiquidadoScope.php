<?php

namespace App\Scopes\Servico;

use App\Enums\LancamentoStatusTipoEnum;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoLiquidadoScope implements Scope
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
                $tableAsNamePagamento = (new ServicoPagamento())->getTableAsName();
                $query->from((new ServicoPagamentoLancamento())->getTableNameAsName())
                    ->selectRaw("COALESCE(SUM(ROUND(CAST({$tableSubAlias}.valor_recebido AS numeric), 2)), 0)")
                    ->join(
                        (new ServicoPagamento())->getTableNameAsName(),
                        "{$tableAsNamePagamento}.servico_id",
                        '=',
                        "{$tableAlias}.id"
                    )
                    ->whereColumn("{$tableSubAlias}.pagamento_id", "{$tableAsNamePagamento}.id")
                    ->whereNull("{$tableSubAlias}.deleted_at")
                    ->whereIn(
                        "{$tableSubAlias}.status_id",
                        LancamentoStatusTipoEnum::statusLiquidadoScope()
                    )
                    ->where("{$tableSubAlias}.tenant_id", tenant('id'))
                    ->whereIn("{$tableSubAlias}.domain_id", TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
            }, 'total_liquidado');
        } else {
            // Se não houver alias, usa o relacionamento direto com 'lancamentos'
            $builder->withSum(['lancamentos as total_liquidado' => function ($query) {
                $table = (new ServicoPagamentoLancamento())->getTable();
                $query->whereIn("{$table}.status_id", [
                    LancamentoStatusTipoEnum::LIQUIDADO->value,
                    LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value,
                    LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value,
                ]);
            }], DB::raw('ROUND(CAST(valor_recebido AS numeric), 2)'));
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoLiquidadoScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
