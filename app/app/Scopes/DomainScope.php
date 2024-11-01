<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Traits\BelongsToDomain;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class DomainScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (! DomainTenantResolver::$currentDomain) {
            return;
        }

        $builder->where($model->qualifyColumn(BelongsToDomain::$domainIdColumn), DomainTenantResolver::$currentDomain->id);
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutDomain', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
