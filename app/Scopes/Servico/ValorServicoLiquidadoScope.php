<?php

namespace App\Scopes\Servico;

use App\Enums\ServicoPagamentoLancamentoStatusTipoEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoLiquidadoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Soma diretamente os valores de 'valor_esperado' na tabela de 'lancamentos'
        $builder->withSum(['lancamentos as total_liquidado' => function ($query) {
            $query->whereIn('status_id', [
                ServicoPagamentoLancamentoStatusTipoEnum::LIQUIDADO->value,
                ServicoPagamentoLancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value
            ]);
        }], DB::raw('ROUND(CAST(valor_recebido AS numeric), 2)'));
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoLiquidadoScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
