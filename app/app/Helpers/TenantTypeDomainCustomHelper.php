<?php

namespace App\Helpers;

use App\Enums\TenantTypeEnum;
use App\Models\Auth\UserTenantDomain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class TenantTypeDomainCustomHelper
{
    public static function getDomainsPorUsuario(): Collection
    {
        $acessos = UserTenantDomain::with('domain')
            ->where('user_id', Auth::id())
            ->get();

        return $acessos->pluck('domain');
    }

    public static function getDomainNameSelected(): string
    {
        switch (tenant('tenant_type_id')) {

            // Se for a identificação manual do domínio, então se retorna o nome do domínio selecionado. Se for 0 (zero), retorna vazio.
            case TenantTypeEnum::ADVOCACIA_MANUAL->value:

                // Obtém o domínio da request (se existir)
                $selectedDomainId = Request::get(config('tenancy_custom.tenant_type.name_attribute_key'));

                // Se for selecionado todos os domínios, então o valor é 0 (zero) e não precisa ser filtrado
                if ($selectedDomainId) {
                    return DomainTenantResolver::$currentDomain->name;
                }
                break;

            default:
                return DomainTenantResolver::$currentDomain->name;
                break;
        }
        return '';
    }
}
