<?php

namespace App\Http\Middleware\Modulo;

use App\Models\Auth\UserTenantDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UsuarioNoTenantMiddleware
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
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant('id');
        if ($tenant && Auth::check()) {
            $userId =  Auth::user()->id;
            $usuarioNoTenant = UserTenantDomain::where('tenant_id', $tenant)->where('user_id', $userId)->first();
            if ($usuarioNoTenant) {
                return $next($request);
            }
        }
        return response()->view('errors.usuario_sem_acesso_modulo');
    }
}

