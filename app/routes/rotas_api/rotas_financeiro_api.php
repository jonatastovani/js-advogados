<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'financeiro',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,financeiro',
        'usuario.tenant',
    ],
], function () {

    Route::prefix('conta')->group(function () {

        Route::controller(App\Http\Controllers\Financeiro\ContaController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.financeiro.conta');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('lancamentos')->group(function () {

        Route::get('', function () {})->name('api.financeiro.lancamentos');

        Route::prefix('servicos')->group(function () {

            Route::controller(App\Http\Controllers\Servico\ServicoPagamentoLancamentoController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');
                Route::post('{uuid}', 'show');
                Route::put('reagendar/{uuid}', 'storeLancamentoReagendadoServico');
            });
        });

        Route::prefix('gerais')->group(function () {

            Route::controller(App\Http\Controllers\Financeiro\LancamentoGeralController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store')->name('api.financeiro.lancamentos.lancamento-geral');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::put('reagendar/{uuid}', 'storeLancamentoReagendado');
                Route::delete('{uuid}', 'destroy');
            });
        });
    });

    Route::prefix('movimentacao-conta')->group(function () {

        Route::get('', function () {})->name('api.financeiro.movimentacao-conta');

        Route::controller(App\Http\Controllers\Financeiro\MovimentacaoContaController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::prefix('lancamentos')->group(function () {

                Route::get('', function () {})->name('api.financeiro.movimentacao-conta.lancamentos');

                Route::prefix('servicos')->group(function () {
                    Route::get('', function () {})->name('api.financeiro.movimentacao-conta.lancamento-servico');
                    Route::post('', 'storeLancamentoServico');
                    Route::post('status-alterar', 'alterarStatusLancamentoServico');
                });

                Route::prefix('gerais')->group(function () {

                    Route::get('', function () {})->name('api.financeiro.movimentacao-conta.lancamento-geral');
                    Route::post('', 'storeLancamentoGeral');
                    Route::post('status-alterar', 'alterarStatusLancamentoGeral');
                });
            });
        });
    });

    Route::prefix('balanco-repasse-parceiros')->group(function () {

        Route::controller(App\Http\Controllers\Financeiro\MovimentacaoContaController::class)->group(function () {

            Route::get('', function () {})->name('api.financeiro.balanco-repasse-parceiros');
            Route::post('consulta-filtros', 'postConsultaFiltrosBalancoRepasseParceiro');
        });
    });

    Route::prefix('tenant')->group(function () {

        Route::prefix('pagamento-tipo-tenant')->group(function () {

            Route::controller(App\Http\Controllers\Financeiro\PagamentoTipoTenantController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store')->name('api.financeiro.pagamento-tipo-tenant');
                Route::match(['get', 'post'], '{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });

        Route::prefix('lancamento-categoria-tipo-tenant')->group(function () {

            Route::controller(App\Http\Controllers\Tenant\LancamentoCategoriaTipoTenantController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store')->name('api.tenant.lancamento-categoria-tipo-tenant');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });
    });
});
