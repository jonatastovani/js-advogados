<?php

namespace App\Scopes;

use App\Enums\TenantTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Traits\BelongsToDomain;
use Illuminate\Support\Facades\Request;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class DomainScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        switch (tenant('tenant_type_id')) {
            // Se for a identificação manual do domínio, então filtra pelo domínio selecionado
            case TenantTypeEnum::ADVOCACIA_MANUAL->value:

                // Obtém o domínio da request (se existir)
                $selectedDomainId = Request::get(config('tenancy_custom.tenant_type.name_attribute_key'));

                // Se for selecionado todos os domínios, então o valor é 0 (zero) e não precisa ser filtrado
                if ($selectedDomainId) {
                    // Filtra pelo domínio selecionado via request
                    $builder->where($model->qualifyColumn(BelongsToDomain::$domainIdColumn), $selectedDomainId);
                }
                break;

            default:

                if (! DomainTenantResolver::$currentDomain) {
                    return;
                }

                // Caso contrário, aplica o domínio identificado automaticamente
                $builder->where($model->qualifyColumn(BelongsToDomain::$domainIdColumn), DomainTenantResolver::$currentDomain->id);
                break;
        }
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutDomain', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
