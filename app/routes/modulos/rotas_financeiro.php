<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Financeiro\FinanceiroController::class)->group(function () {

    Route::prefix('financeiro')->group(function () {

        Route::get('', 'financeiroIndex')->name('financeiro.index');
        Route::get('lancamentos-servicos', 'lancamentosServicosIndex')->name('financeiro.lancamentos-servicos.index');

        Route::prefix('movimentacao-conta')->group(function () {

            Route::get('', 'movimentacaoContaIndex')->name('financeiro.movimentacao-conta.index');
            Route::get('impressao', 'movimentacaoContaImpressao')->name('financeiro.movimentacao-conta.impressao');
        });

        Route::prefix('balanco-repasse-parceiro')->group(function () {

            Route::get('', 'balancoRepasseParceiroIndex')->name('financeiro.balanco-repasse-parceiro.index');
        });
    });
});
