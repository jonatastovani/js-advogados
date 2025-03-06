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

                Route::controller(App\Http\Controllers\Tenant\AnotacaoLembreteTenantController::class)->group(function () {
                    Route::post('', 'storeAnotacaoServico');
                    Route::get('{uuid}', 'show');
                    Route::put('{uuid}', 'updateAnotacaoServico');
                    Route::delete('{uuid}', 'destroy');
                });
            });

            Route::prefix('cliente')->group(function () {

                Route::controller(App\Http\Controllers\Servico\ServicoClienteController::class)->group(function () {

                    Route::get('', 'index');
                    Route::post('', 'store');
                });
            });

            Route::prefix('documento')->group(function () {

                Route::controller(App\Http\Controllers\Comum\DocumentoTenantController::class)->group(function () {

                    Route::get('', 'indexServico');
                    // Route::get('{uuid}', 'show');
                    Route::post('', 'storeServico');
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

                Route::prefix('{pagamento_uuid}')->group(function () {

                    Route::prefix('participacao')->group(function () {

                        Route::controller(App\Http\Controllers\Comum\ParticipacaoController::class)->group(function () {

                            Route::get('', 'indexPagamento');
                            Route::post('', 'storePagamento');
                            Route::delete('', 'destroyPagamento');
                        });
                    });

                    Route::prefix('lancamentos')->group(function () {

                        Route::controller(App\Http\Controllers\Servico\ServicoPagamentoLancamentoController::class)->group(function () {

                            // Route::post('', 'store');
                            Route::get('{uuid}', 'show');
                            Route::put('{uuid}', 'update');
                        });

                        Route::prefix('{lancamento_uuid}/participacao')->group(function () {

                            Route::controller(App\Http\Controllers\Comum\ParticipacaoController::class)->group(function () {

                                Route::get('', 'indexLancamento');
                                Route::post('', 'storeLancamento');
                                Route::delete('', 'destroyLancamento');
                            });
                        });
                    });
                });
            });

            Route::prefix('participacao')->group(function () {

                Route::controller(App\Http\Controllers\Comum\ParticipacaoController::class)->group(function () {

                    Route::get('', 'indexServico');
                    Route::post('', 'storeServico');
                    Route::delete('', 'destroyServico');
                });
            });

            Route::prefix('relatorio/valores')->group(function () {

                Route::controller(App\Http\Controllers\Servico\ServicoController::class)->group(function () {
                    Route::get('', 'getRelatorioValores');
                });
            });
        });
    });

    Route::prefix('comum')->group(function () {

        Route::prefix('participacao-preset')->group(function () {

            Route::controller(App\Http\Controllers\Comum\ParticipacaoPresetController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index')->name('api.comum.participacao-preset');
                Route::post('', 'store');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });
    });
});
