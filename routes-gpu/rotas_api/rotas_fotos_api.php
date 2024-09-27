<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'fotos',
    'controller' => App\Http\Controllers\FotoController::class,
], function () {
   
    Route::get('', 'index')->name('api.fotos');
   
    Route::get('foto-preso/id/{id}', 'buscarFotoPresoId');
    Route::get('foto-funcionario/id/{id}', 'buscarFotoFuncionarioId');
});
