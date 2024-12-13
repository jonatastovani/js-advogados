<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pessoa')->group(function () {

    Route::controller(App\Http\Controllers\View\Pessoa\PessoaController::class)->group(function () {

        Route::prefix('cliente')->group(function () {
            Route::prefix('pessoa-fisica')->group(function () {

                Route::get('', 'clientePessoaFisicaIndex')->name('pessoa.cliente.pessoa-fisica.index');
                Route::get('/form', 'clientePessoaFisicaForm')->name('pessoa.cliente.pessoa-fisica.form');
                Route::get('/form/{uuid}', 'clientePessoaFisicaFormEditar');
            });

            Route::prefix('pessoa-juridica')->group(function () {

                Route::get('', 'clientePessoaJuridicaIndex')->name('pessoa.cliente.pessoa-juridica.index');
                Route::get('/form', 'clientePessoaJuridicaForm')->name('pessoa.cliente.pessoa-juridica.form');
                Route::get('/form/{uuid}', 'clientePessoaJuridicaFormEditar');
            });
        });
    });
});
