<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'referencias',
], function () {

    Route::prefix('area-juridica')->group(function () {

        Route::controller(App\Http\Controllers\Referencias\AreaJuridicaController::class)->group(function () {

            Route::post('select2', 'select2');
            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('{id}', 'show');
            Route::post('', 'store')->name('api.referencias.area-juridica');
            Route::put('{id}', 'update');
        });
    });
});
