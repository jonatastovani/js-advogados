<?php

namespace App\Http\Middleware;

use App\Enums\TenantTypeEnum;
use App\Models\Auth\Domain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

/**
 * Middleware responsável por validar a inicialização manual do domínio para certos tipos de tenants.
 *
 * Funcionalidades:
 * - Verifica se o tenant exige inicialização manual do domínio (`tenant_type_id = X`).
 * - Obtém o ID do domínio selecionado a partir da variável global.
 * - Se não houver um domínio selecionado, retorna uma página de erro.
 */
class CheckManualInitializationTenantDomain
{
    /**
     * Manipula a requisição verificando a configuração de domínio para tenants de inicialização manual.
     *
     * @param  \Illuminate\Http\Request  $request  Requisição HTTP recebida.
     * @param  \Closure  $next  Próximo middleware na pilha de execução.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o domínio pertence a um tenant e se ele exige inicialização manual
        if (tenant('tenant_type_id') == TenantTypeEnum::ADVOCACIA_MANUAL->value) {

            $selectedDomainId = $request->input(config('tenancy_custom.tenant_type.name_attribute_key'));

            if ($selectedDomainId) {
                $domain = tenant()->domains->where('id', $selectedDomainId)->first();

                // Se houver um domínio selecionado, resolve o domínio
                if ($selectedDomainId) {
                    app(DomainTenantResolver::class)->resolved(tenant(), $domain->domain);
                }
            }
        }

        // Prossegue com a execução da requisição
        return $next($request);
    }
}
