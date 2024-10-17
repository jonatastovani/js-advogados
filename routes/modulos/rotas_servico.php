<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Servico\ServicoController::class)->group(function () {

    Route::get('', 'index')->name('advocacia.index');

    Route::prefix('servico')->group(function () {

        Route::get('', 'servicoIndex')->name('servico.index');
        Route::get('/form', 'servicoForm')->name('servico.form');
        Route::get('/form/{uuid}', 'servicoFormEditar');

        Route::prefix('participacao')->group(function () {

            Route::get('', 'participacaoIndex')->name('servico.participacao.index');
            Route::get('/form', 'participacaoForm')->name('servico.participacao.form');
            Route::get('/form/{uuid}', 'participacaoFormEditar');
        });
    });
});
