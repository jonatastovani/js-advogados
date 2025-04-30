<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Relatorio\RelatorioController::class)->group(function () {

    Route::prefix('relatorio')->group(function () {

        Route::get('', 'relatorioIndex')->name('relatorio.index');

        Route::prefix('pagamentos')->group(function () {

            Route::prefix('servicos')->group(function () {
                Route::get('', 'pagamentosServicosIndex')->name('relatorio.pagamentos-servicos.index');
            });
        });
    });
});
