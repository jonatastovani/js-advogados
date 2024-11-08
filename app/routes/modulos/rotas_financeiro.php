<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Financeiro\FinanceiroController::class)->group(function () {

    Route::prefix('financeiro')->group(function () {

        Route::get('', 'financeiroIndex')->name('financeiro.index');
        Route::get('lancamentos-servicos', 'lancamentosServicosIndex')->name('financeiro.lancamentos-servicos.index');
    });
});
