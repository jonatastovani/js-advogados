<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Servico\ServicoController::class)->group(function () {

    Route::get('', 'index')->name('advocacia.index');

    Route::prefix('servico')->group(function () {

        Route::get('', 'servicoIndex')->name('servico.index');
        Route::get('/form', 'servicoForm')->name('advocacia.servico.form');
        Route::get('/form/{uuid}', 'servicoFormEditar');
    });
});
