<?php

namespace App\Scopes\Servico;

use App\Enums\LancamentoStatusTipoEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoEmAnaliseScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->withSum(['lancamentos as total_em_analise' => function ($query) {
            $query->whereIn('status_id', [
                LancamentoStatusTipoEnum::LIQUIDADO_EM_ANALISE->value,
                LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE_EM_ANALISE->value,
                LancamentoStatusTipoEnum::CANCELADO_EM_ANALISE->value,
                LancamentoStatusTipoEnum::REAGENDADO_EM_ANALISE->value,
                LancamentoStatusTipoEnum::INADIMPLENTE_EM_ANALISE->value,
            ]);
        }], DB::raw('ROUND(CAST(valor_esperado AS numeric), 2)'));
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoAguardando', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
