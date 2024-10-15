<?php

namespace App\Scopes\Servico;

use App\Enums\ServicoPagamentoLancamentoStatusTipoEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ValorServicoAguardandoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Soma diretamente os valores de 'valor_esperado' na tabela de 'lancamentos'
        $builder->withSum(['lancamentos as total_aguardando' => function ($query) {
            $query->whereIn('status_id', [
                ServicoPagamentoLancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value,
                ServicoPagamentoLancamentoStatusTipoEnum::PAGAMENTO_REAGENDADO->value,
                ServicoPagamentoLancamentoStatusTipoEnum::LANCADO_PARA_O_FINAL->value,
            ]);
        }], DB::raw('ROUND(CAST(valor_esperado AS numeric), 2)'));
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServicoAguardandoScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
