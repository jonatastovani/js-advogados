<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'financeiro',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,financeiro',
        'usuario.tenant',
    ],
], function () {

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
                Route::put('reagendar/{uuid}', 'updateLancamentoGeralReagendado');
                Route::delete('{uuid}', 'destroy');
            });
        });

        Route::prefix('agendamento')->group(function () {

            Route::controller(App\Http\Controllers\Financeiro\LancamentoAgendamentoController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('', 'index');
                Route::post('', 'store')->name('api.financeiro.lancamentos.lancamento-agendamento');
                Route::get('{uuid}', 'show');
                Route::put('{uuid}', 'update');
                Route::delete('{uuid}', 'destroy');
            });
        });
    });

    Route::prefix('movimentacao-conta')->group(function () {

        Route::controller(App\Http\Controllers\Financeiro\MovimentacaoContaController::class)->group(function () {

            Route::get('', function () {})->name('api.financeiro.movimentacao-conta');
            Route::post('consulta-filtros', 'postConsultaFiltros');
            // Route::post('transferencia-conta', 'storeTransferenciaConta')->name('api.financeiro.movimentacao-conta.transferencia-conta');

            Route::get('{uuid}/documento-gerado', 'getDocumentoGerado');

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

    Route::prefix('repasse-parceiro')->group(function () {

        Route::get('', function () {})->name('api.financeiro.repasse-parceiro');

        Route::controller(App\Http\Controllers\Financeiro\MovimentacaoContaParticipanteController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltrosBalancoRepasseParceiro');
            Route::post('lancar', 'storeLancarRepasseParceiro')->name('api.financeiro.repasse-parceiro.lancar');
        });
    });
});
