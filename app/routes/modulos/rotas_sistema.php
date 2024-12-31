<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Sistema\SistemaController::class)->group(function () {

    Route::prefix('sistema')->group(function () {

        Route::prefix('empresa')->group(function () {

            Route::get('', 'configuracaoEmpresaForm')->name('sistema.configuracao.empresa.form');
        });

        // Route::prefix('preenchimento-automatico')->group(function () {

        //     Route::get('', 'preenchimentoAutomatico')->name('sistema.configuracao.preenchimento-automatico.form');
        // });
    });
});
