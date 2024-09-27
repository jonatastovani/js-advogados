<?php

use Illuminate\Support\Facades\Route;


Route::controller(App\Http\Controllers\View\Admin\AdminController::class)->group(function () {

    Route::get('', 'index')->name('admin.index');

    Route::prefix('usuarios')->name('')->group(function () {
        Route::get('permissoes', 'usuariosPermissoes')->name('admin.usuarios.permissoes');
    });

    Route::prefix('permissoes')->group(function () {
        Route::get('permissoes', 'permissoesPermissoes')->name('admin.permissoes.permissoes');
        // Route::get('grupos', 'permissoesGrupos')->name('admin.permissoes.grupos');
    });

    Route::get('/teste', function () {
        return view('layouts.layout', ['dados' => ['/teste tipo 5']]);
    });
});
