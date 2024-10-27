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

        Route::prefix('{servico_uuid}')->group(function () {

            Route::prefix('anotacao')->group(function () {

                Route::controller(App\Http\Controllers\Servico\ServicoAnotacaoController::class)->group(function () {
                    Route::post('', 'store');
                    Route::get('{uuid}', 'show');
                    Route::put('{uuid}', 'update');
                    Route::delete('{uuid}', 'destroy');
                });
            });

            Route::prefix('pagamentos')->group(function () {

                Route::controller(App\Http\Controllers\Servico\ServicoPagamentoController::class)->group(function () {

                    Route::get('', 'index');
                    Route::post('', 'store');
                    Route::get('{uuid}', 'show');
                    Route::put('{uuid}', 'update');
                    Route::delete('{uuid}', 'destroy');
                });
            });

            Route::prefix('relatorio/valores')->group(function () {

                Route::controller(App\Http\Controllers\Servico\ServicoController::class)->group(function () {
                    Route::get('', 'getRelatorioValores');
                });
            });
        });

        Route::prefix('participacao-preset')->group(function () {

            Route::controller(App\Http\Controllers\Servico\ServicoParticipacaoPresetController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::post('', 'store')->name('api.servico-participacao-preset');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });
    });

    Route::prefix('tenant')->group(function () {

        Route::prefix('area-juridica')->group(function () {

            Route::controller(App\Http\Controllers\Tenant\AreaJuridicaTenantController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store')->name('api.tenant.area-juridica');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
            });
        });

        Route::prefix('servico-atuacao-tipo')->group(function () {

            Route::controller(App\Http\Controllers\Tenant\ServicoParticipacaoTipoTenantController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store')->name('api.tenant.servico-participacao-tipo');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
            });
        });
    });
});
