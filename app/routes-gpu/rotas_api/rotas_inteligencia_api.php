<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'inteligencia',
    'middleware' => [
        'tenant.rota.tipo:4,true,inteligencia',
        'usuario.tenant',
    ],
], function () {

    Route::prefix('info-subj')->group(function () {

        // Route::controller(App\Http\Controllers\Auth\PermissionController::class)->group(function () {
        //     Route::get('', 'index')->name('api.inteligencia.informacao-subjetiva');

        //     Route::get('categoria/{modulo_id}/admin', 'getPermissoesPorModuloComAdmin');
        //     Route::get('modulo/{modulo_id}/admin/exceto-permissao/{permissao_id}', 'getPermissoesPorModuloComAdminExetoPermissao');
        //     Route::post('consulta-filtros', 'postConsultaFiltros');

        //     Route::get('{id}', 'show');
        //     Route::post('', 'store');
        //     Route::put('{id}', 'update');
        //     Route::delete('{id}', 'destroy');

        //     Route::get('/php/{id}', 'renderPhpEnumFront');
        // });

        Route::controller(App\Http\Controllers\GPU\Inteligencia\InformacaoSubjetivaController::class)->group(function () {
            Route::post('consulta-filtros', 'postConsultaFiltros');
    
            Route::get('{uuid}', 'show');
            Route::post('', 'store')->name('api.inteligencia.info-subj');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });

        Route::group([
            'prefix' => 'categoria',
            'controller' => App\Http\Controllers\GPU\Inteligencia\InformacaoSubjetivaCategoriaController::class,
        ], function () {
            Route::post('consulta-filtros', 'postConsultaFiltros');
            Route::post('select2', 'select2');

            Route::get('{uuid}', 'show');
            Route::post('', 'store')->name('api.inteligencia.info-subj.categoria');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });
});
