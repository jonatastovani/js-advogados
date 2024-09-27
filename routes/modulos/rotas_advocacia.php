<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'adv',
    'middleware' => [
        'auth:sanctum',
        // 'tenant.rota.tipo:3,true,advocacia',
        'usuario.tenant',
    ],
    'controller' => \App\Http\Controllers\View\Servico\ServicoController::class
], function () {

    Route::get('', 'index')->name('advocacia.index');

    Route::prefix('servico')->group(function () {
        
        Route::get('', 'servicoIndex')->name('advocacia.servico.index');
        Route::get('/form', 'servicoForm')->name('advocacia.servico.form');
        Route::get('/form/{uuid}', 'servicoFormEditar');
    });
});
