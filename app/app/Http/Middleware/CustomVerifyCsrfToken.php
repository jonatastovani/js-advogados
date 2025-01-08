<?php

namespace App\Http\Middleware;

use App\Common\RestResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class CustomVerifyCsrfToken extends Middleware
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            if ($request->expectsJson()) {
                $response = RestResponse::createErrorResponse(419, 'Sua sessão expirou. Por favor, faça login novamente.');
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }

            // Redireciona para a página de login com uma mensagem
            return redirect()->guest(route('login'))->withErrors(['csrf_error' => 'Sua sessão expirou. Faça login novamente.']);
        }
    }
}
