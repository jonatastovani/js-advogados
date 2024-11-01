<?php

namespace App\Scopes\Servico;

use App\Models\Servico\ServicoPagamento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

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
                $tableSubAlias = ServicoPagamento::getTableAsName();
                $query->from(ServicoPagamento::getTableNameAsName())
                    ->selectRaw("COALESCE(SUM({$tableSubAlias}.valor_total), 0)")
                    ->whereColumn("{$tableSubAlias}.servico_id", "{$tableAlias}.id")
                    ->whereNull("{$tableSubAlias}.deleted_at")
                    ->where("{$tableSubAlias}.tenant_id", tenant('id'))
                    ->where("{$tableSubAlias}.domain_id", DomainTenantResolver::$currentDomain->id);
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
