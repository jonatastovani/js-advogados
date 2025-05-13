<?php

namespace App\Http\Middleware;

use App\Models\Auth\UserTenantDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExistingUserTenantDomainMiddleware
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
            $usuarioNoTenant = UserTenantDomain::where('tenant_id', $tenant)
                ->where('user_id', $userId)
                ->where('ativo_bln', true)
                ->first();
            if ($usuarioNoTenant) {
                return $next($request);
            }
        }

        // Retorna resposta condicional com base no tipo de requisição
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuário não possui acesso à Unidade\Domínio solicitado.',
                'code' => 403
            ], 403);
        }

        return response()->view('errors.usuario_sem_unidade_de_acesso');
    }
}
