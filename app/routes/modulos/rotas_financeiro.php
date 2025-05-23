<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Financeiro\FinanceiroController::class)->group(function () {

    Route::prefix('financeiro')->group(function () {

        Route::get('', 'financeiroIndex')->name('financeiro.index');

        Route::prefix('lancamentos')->group(function () {

            Route::prefix('agendamentos')->group(function () {

                Route::get('', 'lancamentosAgendamentosIndex')->name('financeiro.lancamentos-agendamentos.index');
            });

            Route::prefix('gerais')->group(function () {

                Route::get('', 'lancamentosGeraisIndex')->name('financeiro.lancamentos-gerais.index');
            });

            Route::prefix('ressarcimentos')->group(function () {

                Route::get('', 'lancamentosRessarcimentosIndex')->name('financeiro.lancamentos-ressarcimentos.index');
            });

            Route::prefix('servicos')->group(function () {

                Route::get('', 'lancamentosServicosIndex')->name('financeiro.lancamentos-servicos.index');
            });
        });

        Route::prefix('movimentacao-conta')->group(function () {

            Route::get('', 'movimentacaoContaIndex')->name('financeiro.movimentacao-conta.index');
            Route::get('impressao', 'movimentacaoContaImpressao')->name('financeiro.movimentacao-conta.impressao');
        });

        Route::prefix('balanco-repasse')->group(function () {

            Route::get('', 'balancoRepasseIndex')->name('financeiro.balanco-repasse.index');
            Route::get('impressao', 'balancoRepasseImpressao')->name('financeiro.balanco-repasse.impressao');
        });

        Route::prefix('painel-contas')->group(function () {

            Route::get('', 'painelContasIndex')->name('financeiro.painel-contas.index');
        });
    });
});
