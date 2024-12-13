<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'pessoa',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,pessoa',
        'usuario.tenant',
    ],
], function () {

    Route::controller(App\Http\Controllers\Pessoa\PessoaController::class)->group(function () {

        Route::post('consulta-filtros/pessoa-fisica', 'postConsultaFiltros');
        Route::post('consulta-filtros/pessoa-juridica', 'postConsultaFiltrosJuridica');

        Route::post('', 'store')->name('api.pessoa');
        Route::get('{uuid}', 'show');
        Route::put('{uuid}', 'update');
        Route::delete('{uuid}', 'destroy');
    });
});
