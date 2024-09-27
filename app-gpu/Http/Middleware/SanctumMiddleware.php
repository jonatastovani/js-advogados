<?php

namespace App\Http\Middleware;

use App\Common\RestResponse;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SanctumMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token) {
            $token = PersonalAccessToken::findToken($token);

            if ($token) {
                $sessionUserData = json_decode($token->session_user_data, true);
                $request->merge(['session_user_data' => $sessionUserData]);
            }
        }

        return $next($request);
    }
}
