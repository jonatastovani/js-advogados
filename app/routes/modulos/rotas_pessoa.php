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

            Route::prefix('parceiro')->group(function () {

                Route::get('', 'pessoaFisicaParceiroIndex')->name('pessoa.pessoa-fisica.parceiro.index');
                Route::get('/form', 'pessoaFisicaParceiroForm')->name('pessoa.pessoa-fisica.parceiro.form');
                Route::get('/form/{uuid}', 'pessoaFisicaParceiroFormEditar');
            });
        });

        Route::prefix('pessoa-juridica')->group(function () {

            Route::prefix('cliente')->group(function () {

                Route::get('', 'pessoaJuridicaClienteIndex')->name('pessoa.pessoa-juridica.cliente.index');
                Route::get('/form', 'pessoaJuridicaClienteForm')->name('pessoa.pessoa-juridica.cliente.form');
                Route::get('/form/{uuid}', 'pessoaJuridicaClienteFormEditar');
            });
        });
    });
});
