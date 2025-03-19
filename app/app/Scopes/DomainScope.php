<?php

namespace App\Scopes;

use App\Helpers\TenantTypeDomainCustomHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Traits\BelongsToDomain;

class DomainScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn($model->qualifyColumn(BelongsToDomain::$domainIdColumn), TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain());
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutDomain', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
