<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearModalSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $requestUuid = $request->input('request_uuid');
        // Limpa os dados de modal associados ao request_uuid após a requisição
        $modalLoaded = session()->get('modals_loaded', []);

        foreach ($modalLoaded as $key => $value) {
            if($key == $requestUuid) {
                unset($modalLoaded[$key]);
                session()->put('modals_loaded', $modalLoaded); // Atualiza a sessão sem esse uuid
                break;
            }
        }
        
        return $response;
    }
}
