<?php

use App\Http\Middleware\ExistingUserTenantDomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'relatorio',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,relatorio',
        // 'usuario.tenant',
        ExistingUserTenantDomainMiddleware::class,
    ],
], function () {

    Route::prefix('pagamentos')->group(function () {

        Route::get('', function () {})->name('api.relatorio.pagamentos');

        Route::prefix('servicos')->group(function () {

            Route::controller(App\Http\Controllers\Servico\ServicoPagamentoController::class)->group(function () {
                Route::post('consulta-filtros', 'postConsultaFiltros');

                // Route::get('', 'index');
                // Route::post('', 'store');
                // Route::get('{uuid}', 'show');
                // Route::put('{uuid}', 'update');
                // Route::delete('{uuid}', 'destroy');
            });
        });
    });
});
