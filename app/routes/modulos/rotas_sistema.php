<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Sistema\SistemaController::class)->group(function () {

    Route::prefix('sistema')->group(function () {

        Route::get('', 'sistemaIndex')->name('sistema.index');

        Route::prefix('empresa')->group(function () {

            Route::get('', 'configuracaoEmpresaForm')->name('sistema.configuracao.empresa.form');
        });

    });
});
