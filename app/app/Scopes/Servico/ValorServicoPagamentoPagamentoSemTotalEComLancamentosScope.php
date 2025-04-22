<?php

namespace App\Scopes\Servico;

use App\Enums\PagamentoTipoEnum;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Tenant\PagamentoTipoTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoPagamentoSemTotalEComLancamentosScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tableAlias = $builder->getQuery()->from;
        $lancamentoModel = new ServicoPagamentoLancamento();
        $pagamentoModel = new ServicoPagamento();
        $lancamentoTable = $lancamentoModel->getTable();
        $pagamentoTipoTenantModel = new PagamentoTipoTenant();

        if (strpos($tableAlias, ' as ') !== false) {
            [, $tableAlias] = explode(' as ', $tableAlias);
            $tableAlias = trim($tableAlias);

            $builder->selectSub(function ($query) use ($tableAlias, $lancamentoTable, $pagamentoModel, $pagamentoTipoTenantModel) {
                $query->from($lancamentoTable)
                    ->selectRaw("COALESCE(SUM(ROUND(CAST(valor_esperado AS numeric), 2)), 0)")
                    ->whereIn('pagamento_id', function ($sub) use ($pagamentoModel, $pagamentoTipoTenantModel) {
                        $sub->from($pagamentoModel->getTable())
                            ->select('id')
                            ->whereIn('pagamento_tipo_tenant_id', function ($sub2) use ($pagamentoModel, $pagamentoTipoTenantModel) {
                                $sub2->from($pagamentoTipoTenantModel->getTable())
                                    ->select('id')
                                    ->whereIn('pagamento_tipo_id', PagamentoTipoEnum::pagamentoTipoSemTotalDefinidoEComLancamentos())
                                    ->whereNull('deleted_at');
                            })
                            ->whereNull('deleted_at');
                    })
                    ->whereColumn('pagamento_id', "{$tableAlias}.id")
                    ->whereNull('deleted_at')
                    ->where('tenant_id', tenant('id'))
                    ->whereIn('domain_id', TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
            }, 'total_pagamento_sem_total');
        } else {
            $builder->withSum(['lancamentos as total_pagamento_sem_total' => function ($query) use ($pagamentoModel, $pagamentoTipoTenantModel) {
                $query->whereIn('pagamento_id', function ($sub) use ($pagamentoModel, $pagamentoTipoTenantModel) {
                    $sub->from($pagamentoModel->getTable())
                        ->select('id')
                        ->whereIn('pagamento_tipo_tenant_id', function ($sub2) use ($pagamentoTipoTenantModel) {
                            $sub2->from($pagamentoTipoTenantModel->getTable())
                                ->select('id')
                                ->whereIn('pagamento_tipo_id', PagamentoTipoEnum::pagamentoTipoSemTotalDefinidoEComLancamentos())
                                ->whereNull('deleted_at');
                        })
                        ->whereNull('deleted_at');
                });
            }], DB::raw('ROUND(CAST(valor_esperado AS numeric), 2)'));
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoPagamentoSemTotalEComLancamentos', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
