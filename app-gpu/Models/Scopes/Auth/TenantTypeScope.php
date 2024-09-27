<?php

namespace App\Models\Scopes\Auth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantTypeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Para não mostrar os tipos Administrador, Api e Secretaria
        $model->where('id', '>', 3);
    }
}
