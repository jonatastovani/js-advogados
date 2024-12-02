<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'referencias',
], function () {

    Route::prefix('conta-subtipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\ContaSubtipoController::class)->group(function () {

            Route::post('select2', 'select2');
            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.referencias.conta-subtipo');
            Route::get('{id}', 'show');
            Route::put('{id}', 'update');
        });
    });

    Route::prefix('conta-status-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\ContaStatusTipoController::class)->group(function () {

            Route::post('select2', 'select2');
            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.referencias.conta-status-tipo');
            Route::get('{id}', 'show');
            Route::put('{id}', 'update');
        });
    });

    Route::prefix('lancamentos-status-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\LancamentoStatusTipoController::class)->group(function () {

            // Route::post('select2', 'select2');
            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index')->name('api.referencias.lancamento-status-tipo');
            Route::get('{id}', 'show');
        });
    });

    Route::prefix('movimentacao-conta-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\MovimentacaoContaTipoController::class)->group(function () {

            Route::get('', 'index')->name('api.referencias.movimentacao-conta-tipo');
        });
    });

    Route::prefix('movimentacao-conta-status-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\MovimentacaoContaStatusTipoController::class)->group(function () {

            Route::get('', 'index')->name('api.referencias.movimentacao-conta-status-tipo');
        });
    });

    Route::prefix('pagamento-status-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\PagamentoStatusTipoController::class)->group(function () {

            // Route::post('select2', 'select2');
            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index')->name('api.referencias.pagamento-status-tipo');
            Route::get('{id}', 'show');
        });
    });
});
