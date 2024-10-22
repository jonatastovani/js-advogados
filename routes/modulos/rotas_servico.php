<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Servico\ServicoController::class)->group(function () {

    Route::get('', 'index')->name('advocacia.index');

    Route::prefix('servico')->group(function () {

        Route::get('', 'servicoIndex')->name('servico.index');
        Route::get('/form', 'servicoForm')->name('servico.form');
        Route::get('/form/{uuid}', 'servicoFormEditar');

        Route::prefix('participacao-preset')->group(function () {

            Route::get('', 'participacaoPresetIndex')->name('servico.participacao.index');
            Route::get('/form', 'participacaoPresetForm')->name('servico.participacao.form');
            Route::get('/form/{uuid}', 'participacaoPresetFormEditar');
        });
    });
});
