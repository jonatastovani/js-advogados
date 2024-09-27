<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'adv',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,advocacia',
        'usuario.tenant',
    ],
], function () {

    Route::prefix('servico')->group(function () {

        Route::controller(App\Http\Controllers\Servico\ServicoController::class)->group(function () {

            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('{id}', 'show');
            Route::post('', 'store')->name('api.servico');
            Route::put('{id}', 'update');
            // Route::delete('{id}', 'destroy');

        });
    });
});
