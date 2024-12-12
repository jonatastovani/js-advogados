<?php

namespace App\Scopes\Servico;

use App\Enums\LancamentoStatusTipoEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoPagamentoInadimplenteScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->withSum(['lancamentos as total_inadimplente' => function ($query) {
            $query->whereIn('status_id', [
                LancamentoStatusTipoEnum::INADIMPLENTE->value,
            ]);
        }], DB::raw('ROUND(CAST(valor_esperado AS numeric), 2)'));
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoPagamentoInadimplente', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
