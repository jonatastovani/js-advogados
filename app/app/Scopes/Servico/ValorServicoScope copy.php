<?php

namespace App\Scopes\Servico;

use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Servico\ServicoPagamento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ValorServicoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Pega o alias da tabela se existir, ou usa o nome original da tabela
        $tableAlias = $builder->getQuery()->from;

        if (strpos($tableAlias, ' as ') !== false) {
            // Pega o alias, se estiver presente
            [$tableName, $alias] = explode(' as ', $tableAlias);
            $tableAlias = trim($alias); // Aplica o alias

            $builder->selectSub(function ($query) use ($tableAlias) {
                $tableSubAlias = (new ServicoPagamento())->getTableAsName();
                $query->from((new ServicoPagamento())->getTableNameAsName())
                    ->selectRaw("COALESCE(SUM({$tableSubAlias}.valor_total), 0)")
                    ->whereColumn("{$tableSubAlias}.servico_id", "{$tableAlias}.id")
                    ->whereNull("{$tableSubAlias}.deleted_at")
                    ->where("{$tableSubAlias}.tenant_id", tenant('id'))
                    ->whereIn("{$tableSubAlias}.domain_id", TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
            }, 'valor_servico');
        } else {
            $builder->withSum('pagamento as valor_servico', 'valor_total');
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServico', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
