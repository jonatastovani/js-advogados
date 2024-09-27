<?php

use App\Http\Controllers\View\Inteligencia\InteligenciaController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'inteligencia',
    'middleware' => [
        'auth:sanctum',
        'tenant.rota.tipo:4,true,inteligencia',
        'usuario.tenant',
    ],
    'controller' => InteligenciaController::class,
], function () {

    Route::get('', 'index')->name('inteligencia.index');
    
    Route::prefix('info-subj')->name('')->group(function () {
        Route::get('', 'informacaoSubjetivaIndex')->name('inteligencia.informacao-subjetiva.index');
        Route::get('/form', 'informacaoSubjetivaForm')->name('inteligencia.informacao-subjetiva.form');
        Route::get('/form/{uuid}', 'informacaoSubjetivaFormEditar');
    });
    
});
