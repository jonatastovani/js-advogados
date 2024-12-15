<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pessoa')->group(function () {

    Route::controller(App\Http\Controllers\View\Pessoa\PessoaController::class)->group(function () {

        Route::prefix('pessoa-fisica')->group(function () {

            Route::prefix('cliente')->group(function () {

                Route::get('', 'pessoaFisicaClienteIndex')->name('pessoa.pessoa-fisica.cliente.index');
                Route::get('/form', 'pessoaFisicaClienteForm')->name('pessoa.pessoa-fisica.cliente.form');
                Route::get('/form/{uuid}', 'pessoaFisicaClienteFormEditar');
            });

            Route::prefix('pessoa-juridica')->group(function () {

                Route::prefix('cliente')->group(function () {

                    Route::get('', 'clientePessoaJuridicaIndex')->name('pessoa.pessoa-juridica.cliente.index');
                    Route::get('/form', 'clientePessoaJuridicaForm')->name('pessoa.pessoa-juridica.cliente.form');
                    Route::get('/form/{uuid}', 'clientePessoaJuridicaFormEditar');
                });
            });
        });
    });
});
