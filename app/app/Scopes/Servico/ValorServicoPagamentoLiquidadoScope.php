<?php

namespace App\Scopes\Servico;

use App\Enums\ServicoPagamentoLancamentoStatusTipoEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoLiquidadoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->withSum(['lancamentos as total_liquidado' => function ($query) {
            $query->whereIn('status_id', [
                ServicoPagamentoLancamentoStatusTipoEnum::LIQUIDADO->value,
                ServicoPagamentoLancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value
            ]);
        }], DB::raw('ROUND(CAST(valor_recebido AS numeric), 2)'));
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoLiquidado', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
