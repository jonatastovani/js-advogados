<?php

namespace App\Scopes\Servico;

use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\PagamentoTipoEnum;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Tenant\PagamentoTipoTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoSemTotalEComLancamentosScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tableAlias = $builder->getQuery()->from;

        $lancamentoModel = new ServicoPagamentoLancamento();
        $pagamentoModel = new ServicoPagamento();
        $pagamentoTipoTenantModel = new PagamentoTipoTenant();

        if (strpos($tableAlias, ' as ') !== false) {
            [, $tableAlias] = explode(' as ', $tableAlias);
            $tableAlias = trim($tableAlias);

            $builder->selectSub(function ($query) use (
                $tableAlias,
                $lancamentoModel,
                $pagamentoModel,
                $pagamentoTipoTenantModel,
            ) {
                // Subquery para buscar os IDs dos pagamentos do tipo que NÃO têm valor total definido
                $idsPagamentosValidos = DB::table($pagamentoModel->getTable())
                    ->select('id')
                    ->whereIn('pagamento_tipo_tenant_id', function ($subquery) use ($pagamentoTipoTenantModel) {
                        $subquery->from($pagamentoTipoTenantModel->getTable())
                            ->select('id')
                            ->whereIn('pagamento_tipo_id', PagamentoTipoEnum::pagamentoTipoSemTotalDefinidoEComLancamentos())
                            ->whereNull('deleted_at');
                    })
                    ->whereColumn('servico_id', "{$tableAlias}.id")
                    ->whereNull('deleted_at');

                // Soma apenas dos lançamentos relacionados a esses pagamentos
                $query->from($lancamentoModel->getTableNameAsName())
                    ->selectRaw("COALESCE(SUM(ROUND(CAST(valor_esperado AS numeric), 2)), 0)")
                    ->whereIn('pagamento_id', $idsPagamentosValidos)
                    ->whereNotIn("{$lancamentoModel->getTableAsName()}.status_id", LancamentoStatusTipoEnum::statusNaoSomarPagamentoSemValorTotalScope())
                    ->where('tenant_id', tenant('id'))
                    ->whereNull('deleted_at')
                    ->whereIn('domain_id', TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
            }, 'total_pagamento_sem_total');
        } else {
            // Fallback sem alias
            $builder->withSum(['lancamentos as total_pagamento_sem_total' => function ($query) use ($pagamentoModel, $pagamentoTipoTenantModel, $lancamentoModel) {
                $query->whereIn('pagamento_id', function ($subquery) use ($pagamentoModel, $pagamentoTipoTenantModel) {
                    $subquery->from($pagamentoModel->getTable())
                        ->select('id')
                        ->whereIn('pagamento_tipo_tenant_id', function ($sub) use ($pagamentoTipoTenantModel) {
                            $sub->from($pagamentoTipoTenantModel->getTable())
                                ->select('id')
                                ->whereIn('pagamento_tipo_id', PagamentoTipoEnum::pagamentoTipoSemTotalDefinidoEComLancamentos())
                                ->whereNull('deleted_at');
                        })
                        ->whereNull('deleted_at');
                })
                    ->whereNotIn("{$lancamentoModel->getTable()}.status_id", LancamentoStatusTipoEnum::statusNaoSomarPagamentoSemValorTotalScope());
            }], DB::raw('ROUND(CAST(valor_esperado AS numeric), 2)'));
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoSemTotalEComLancamentos', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
