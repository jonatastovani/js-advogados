<?php

namespace App\Http\Middleware;

use App\Common\RestResponse;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class CustomVerifyCsrfToken extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log para debugging
            Log::error('CSRF token inválido ou sessão expirada.', [
                'session_token' => session()->token(),
                'request_token' => $request->input('_token'),
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            if ($request->expectsJson()) {
                $response = RestResponse::createErrorResponse(
                    419,
                    'Sua sessão expirou. Por favor, faça login novamente.'
                );
                return response()->json($response->toArray(), 419)->throwResponse();
            }

            // Redireciona para o login com uma mensagem de erro
            return redirect()->route('login')
                ->withErrors(['csrf_error' => 'Sua sessão expirou. Por favor, faça login novamente.'])
                ->withInput(); // Retorna os dados para o formulário
        }
    }
}
