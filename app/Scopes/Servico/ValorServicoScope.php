<?php

namespace App\Scopes\Servico;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ValorServicoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->withSum('pagamento as valor_servico', 'valor_total');
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutValorServico', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
