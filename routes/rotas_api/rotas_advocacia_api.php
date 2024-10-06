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

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::post('', 'store')->name('api.servico');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });

        Route::prefix('{servico_uuid}/anotacao')->group(function () {

            Route::controller(App\Http\Controllers\Servico\ServicoAnotacaoController::class)->group(function () {

                // Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });

        Route::prefix('{servico_uuid}/valor')->group(function () {

            Route::controller(App\Http\Controllers\Servico\ServicoAnotacaoController::class)->group(function () {
                Route::get('', 'index');
                Route::post('', 'store');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });
    });
});
