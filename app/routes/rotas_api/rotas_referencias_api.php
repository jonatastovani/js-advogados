<?php

use App\Http\Middleware\ExistingUserTenantDomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'referencias',
    // 'usuario.tenant',
    ExistingUserTenantDomainMiddleware::class,
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

    Route::prefix('documento-modelo-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\DocumentoModeloTipoController::class)->group(function () {

            // Route::post('select2', 'select2');
            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index')->name('api.referencias.documento-modelo-tipo');
            // Route::post('', 'store');
            Route::get('{id}', 'show');
            // Route::put('{id}', 'update');
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

    Route::prefix('pagamento-status-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\PagamentoStatusTipoController::class)->group(function () {

            // Route::post('select2', 'select2');
            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index')->name('api.referencias.pagamento-status-tipo');
            Route::get('{id}', 'show');
        });
    });

    Route::prefix('chave-pix-tipo')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\ChavePixTipoController::class)->group(function () {

            // Route::post('select2', 'select2');
            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index')->name('api.referencias.chave-pix-tipo');
            Route::get('{id}', 'show');
        });
    });
});
