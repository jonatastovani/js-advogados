<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'middleware' => [
        'tenant.rota.tipo:1,true,admin',
        'usuario.tenant',
    ],
], function () {

    Route::prefix('permissoes')->group(function () {

        Route::controller(App\Http\Controllers\Auth\PermissionController::class)->group(function () {
            Route::get('', 'index')->name('api.admin.permissoes');

            Route::get('modulo/{modulo_id}/admin', 'getPermissoesPorModuloComAdmin');
            Route::get('modulo/{modulo_id}/admin/exceto-permissao/{permissao_id}', 'getPermissoesPorModuloComAdminExetoPermissao');
            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('{id}', 'show');
            Route::post('', 'store');
            Route::put('{id}', 'update');
            Route::delete('{id}', 'destroy');

            Route::get('/php/{id}', 'renderPhpEnumFront');
        });

        Route::controller(App\Http\Controllers\Auth\PermissionGroupController::class)->group(function () {
            Route::prefix('grupos')->group(function () {
                Route::get('', 'index')->name('api.admin.permissoes.grupos');

                Route::get('modulo/{id}', 'getGruposPorModulo');
                Route::get('modulo/{modulo_id}/exceto-grupo/{grupo_id}', 'getGruposPorModuloExetoGrupo');
                Route::post('consulta-filtros', 'postConsultaFiltros');

                Route::get('{id}', 'show');
                Route::post('', 'store');
                Route::put('{id}', 'update');
                Route::delete('{id}', 'destroy');

                Route::get('/php/{id}', 'renderPhpEnumFront');
            });
        });
    });

    Route::controller(App\Http\Controllers\Auth\PermissionModuleController::class)->group(function () {

        Route::get('modulos', 'getModulos')->name('api.admin.modulos');
    });
});
