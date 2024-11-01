<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'adv',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,advocacia',
        'usuario.tenant',
    ],
], function () {

    Route::prefix('pessoa')->group(function () {

        Route::controller(App\Http\Controllers\Pessoa\PessoaController::class)->group(function () {

            Route::post('consulta-filtros/pessoa-fisica', 'postConsultaFiltros');
            Route::post('consulta-filtros/pessoa-juridica', 'postConsultaFiltrosJuridica');

            Route::post('', 'store')->name('api.pessoa');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });
});
