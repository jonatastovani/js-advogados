<?php

namespace App\Scopes\Tenant;

use App\Models\Financeiro\MovimentacaoConta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class SaldoTotalContaTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Obtém o alias da tabela principal ou o nome original
        $tableAlias = $builder->getQuery()->from;

        if (strpos($tableAlias, ' as ') !== false) {
            [$tableName, $alias] = explode(' as ', $tableAlias);
            $tableAlias = trim($alias);
        }

        $tableMovimentacao = (new MovimentacaoConta())->getTable(); // Nome da tabela de movimentações

        // Adiciona a soma dos saldos das últimas movimentações de cada conta_domain_id
        $builder->addSelect([
            'saldo_total' => MovimentacaoConta::selectRaw("COALESCE(SUM(saldo_atualizado), 0)")
                ->whereIn("id", function ($query) use ($tableMovimentacao) {
                    $query->selectRaw("id")
                        ->from($tableMovimentacao . ' as m1')
                        ->whereRaw("m1.created_at = (
                            SELECT MAX(m2.created_at) 
                            FROM {$tableMovimentacao} as m2 
                            WHERE m2.conta_domain_id = m1.conta_domain_id
                        )")
                        ->groupBy("m1.conta_domain_id", "m1.id"); // Agrupamento correto
                })
                ->whereIn("conta_domain_id", function ($query) use ($tableMovimentacao, $tableAlias) {
                    $query->selectRaw("id")
                        ->from($tableMovimentacao)
                        ->whereColumn("conta_id", "{$tableAlias}.id");
                })
        ]);
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutSaldoContaTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
