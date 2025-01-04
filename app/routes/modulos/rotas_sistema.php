<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Sistema\SistemaController::class)->group(function () {

    Route::prefix('sistema')->group(function () {

        Route::prefix('configuracao')->group(function () {

            Route::get('', 'sistemaConfiguracaoForm')->name('sistema.configuracao.form');
        });

        Route::prefix('empresa')->group(function () {

            Route::get('', 'sistemaDadosDaEmpresaForm')->name('sistema.dados-da-empresa.form');
        });


        // Route::prefix('preenchimento-automatico')->group(function () {

        //     Route::get('', 'preenchimentoAutomatico')->name('sistema.configuracao.preenchimento-automatico.form');
        // });
    });
});
