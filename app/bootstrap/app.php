<?php

use App\Common\RestResponse;
use App\Jobs\LancamentoAgendamentoJob;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByPathException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->redirectGuestsTo('/login');
        // $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
        $middleware->alias([
            'sanctum.verify' => \App\Http\Middleware\SanctumMiddleware::class,
            'tenant.rota.tipo' => \App\Http\Middleware\Modulo\RotaEspecificaPorTipoTenantMiddleware::class,
            'usuario.tenant' => \App\Http\Middleware\Modulo\UsuarioNoTenantMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new LancamentoAgendamentoJob)
            ->everyTenSeconds()
            ->withoutOverlapping(); // Garante que o job não será executado novamente antes de terminar o anterior
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();
        $exceptions->level(PDOException::class, LogLevel::CRITICAL);
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $response = RestResponse::createErrorResponse(405, $e->getMessage());
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
        });
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                $response = RestResponse::createErrorResponse(401, "Usuário não autenticado.");
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
        });
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                $response = RestResponse::createErrorResponse(403, $e->getMessage());
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            }
        });
        $exceptions->render(function (TenantCouldNotBeIdentifiedByPathException $e, Request $request) {
            if ($request->is('api/*')) {
                $response = RestResponse::createErrorResponse(403, $e->getMessage());
                return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
            } else {
                return view('errors.rota_nao_encontrada');
            }
        });
        // $exceptions->render(function (RouteNotFoundException $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         $response = RestResponse::createErrorResponse(404, $e->getMessage());
        //         return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
        //     }
        // });
    })->create()
;
