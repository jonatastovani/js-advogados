<?php

use App\Http\Middleware\ExistingUserTenantDomainMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'pessoa',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,pessoa',
        // 'usuario.tenant',
        ExistingUserTenantDomainMiddleware::class,
    ],
], function () {

    Route::controller(App\Http\Controllers\Pessoa\PessoaController::class)->group(function () {

        Route::get('', function () {})->name('api.pessoa');

        Route::get('{uuid}', 'show');
        Route::get('empresa', 'showEmpresa')->name('api.pessoa.empresa'); // Busca a empresa do domínio   
        Route::delete('{uuid}', 'destroy');
    });

    Route::controller(App\Http\Controllers\Pessoa\PessoaPerfilController::class)->group(function () {

        Route::prefix('perfil')->group(function () {
            Route::get('', function () {})->name('api.pessoa.perfil');

            // Route::get('{uuid}', 'show');
            Route::get('empresa', 'showEmpresa')->name('api.pessoa.perfil.empresa'); // Busca a empresa do domínio
            // Route::delete('{uuid}', 'destroy');
        });
    });

    Route::controller(App\Http\Controllers\Pessoa\PessoaFisicaController::class)->group(function () {

        Route::prefix('pessoa-fisica')->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::post('', 'store')->name('api.pessoa.pessoa-fisica');
            Route::put('{uuid}', 'update');
            // Route::delete('{uuid}', 'destroy');
        });
    });

    Route::controller(App\Http\Controllers\Pessoa\PessoaJuridicaController::class)->group(function () {

        Route::prefix('pessoa-juridica')->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::post('', 'store')->name('api.pessoa.pessoa-juridica');
            Route::put('{uuid}', 'update');
            // Route::delete('{uuid}', 'destroy');
        });
    });
});
