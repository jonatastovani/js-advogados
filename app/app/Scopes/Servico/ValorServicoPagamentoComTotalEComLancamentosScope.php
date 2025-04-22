<?php

namespace App\Scopes\Servico;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Servico\ServicoPagamento;
use App\Models\Tenant\PagamentoTipoTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoComTotalEComLancamentosScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tableAlias = $builder->getQuery()->from;
        $pagamentoModel = new ServicoPagamento();
        $pagamentoTipoTenantModel = new PagamentoTipoTenant();

        if (strpos($tableAlias, ' as ') !== false) {
            [, $tableAlias] = explode(' as ', $tableAlias);
            $tableAlias = trim($tableAlias);

            $builder->selectSub(function ($query) use ($tableAlias, $pagamentoModel, $pagamentoTipoTenantModel) {
                $query->from($pagamentoModel->getTable())
                    ->selectRaw('COALESCE(SUM(ROUND(CAST(valor_total AS numeric), 2)), 0)')
                    ->whereIn('pagamento_tipo_tenant_id', function ($sub) use ($pagamentoTipoTenantModel) {
                        $sub->from($pagamentoTipoTenantModel->getTable())
                            ->select('id')
                            ->whereNotIn('pagamento_tipo_id', PagamentoTipoEnum::pagamentoTipoSemTotalDefinidoEComLancamentos())
                            ->whereNull('deleted_at');
                    })
                    ->whereColumn('servico_id', "{$tableAlias}.id")
                    ->whereNull('deleted_at')
                    ->where('tenant_id', tenant('id'))
                    ->whereIn('domain_id', TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
            }, 'total_pagamento_com_total');
        } else {
            $builder->withSum(['pagamento as total_pagamento_com_total' => function ($query) use ($pagamentoTipoTenantModel) {
                $query->whereIn('pagamento_tipo_tenant_id', function ($sub) use ($pagamentoTipoTenantModel) {
                    $sub->from($pagamentoTipoTenantModel->getTable())
                        ->select('id')
                        ->whereNotIn('pagamento_tipo_id', PagamentoTipoEnum::pagamentoTipoSemTotalDefinidoEComLancamentos())
                        ->whereNull('deleted_at');
                });
            }], DB::raw('ROUND(CAST(valor_total AS numeric), 2)'));
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoComTotalEComLancamentos', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
