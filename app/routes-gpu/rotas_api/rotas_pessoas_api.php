<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pessoas')->group(function () {

    Route::controller(App\Http\Controllers\Comum\BuscasDePessoasController::class)->group(function () {
        Route::get('', 'index')->name('api.pessoas');
        Route::post('consulta-filtros', 'postConsultaFiltros')->name('api.pessoa.filtros');
        Route::post('consulta-criterios', 'postConsultaCriterios');
    });

    Route::prefix('gepen')->group(function () {

        Route::controller(App\Http\Controllers\GEPEN\PessoaGEPENController::class)->group(function () {

            Route::get('{id}', 'show');

            Route::prefix('servidor')->group(function () {

                Route::get('{id}', 'showServidor');
            });
        });
    });

    Route::prefix('gpu')->group(function () {

        Route::controller(App\Http\Controllers\GPU\PessoaGPUController::class)->group(function () {
            Route::get('pessoa-por-id/{id}', 'show');
            Route::post('buscar-por-documento', 'buscarPorDocumento');
        });
    });

    Route::prefix('funcionario')->group(function () {

        Route::prefix('gepen')->group(function () {

            Route::controller(App\Http\Controllers\GEPEN\ServidorPessoaGEPENController::class)->group(function () {

                Route::get('rh/{id}', 'show');
            });
        });

        Route::prefix('gpu')->group(function () {

            Route::controller(App\Http\Controllers\GPU\FuncionarioGPUController::class)->group(function () {

                Route::get('rh/{id}', 'show');
            });
        });
    });

});
