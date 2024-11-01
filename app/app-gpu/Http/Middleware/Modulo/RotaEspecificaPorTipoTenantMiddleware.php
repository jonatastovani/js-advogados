<?php

namespace App\Http\Middleware\Modulo;

use App\Models\Auth\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RotaEspecificaPorTipoTenantMiddleware 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $tipoTenant (Obrigatório) O tipo de tenant.
     * @param  bool  $initialize (Opcional) Inicializar o tenant. Padrão é false.
     * @param  string  $tenantId (Opcional) ID do tenant. Padrão é o ID da rota do parametro {tenant}.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $tipoTenant, $initialize = false, $tenantId = ''): Response
    {
        $tenantId = $tenantId ?: $request->route()->parameter('tenant');
        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            if ($tenant->tenant_type_id == $tipoTenant) {
                if ($initialize) {
                    tenancy()->initialize($tenant);
                }
                return $next($request);
            }
        }
        return response()->view('errors.unidade_tenant_nao_encontrado');
    }
}
