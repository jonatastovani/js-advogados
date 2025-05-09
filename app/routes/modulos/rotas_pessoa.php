<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pessoa')->group(function () {

    Route::controller(App\Http\Controllers\View\Pessoa\PessoaController::class)->group(function () {

        Route::prefix('pessoa-fisica')->group(function () {

            Route::get('', 'pessoaFisicaIndex')->name('pessoa.pessoa-fisica.index');
            Route::get('/form', 'pessoaFisicaForm')->name('pessoa.pessoa-fisica.form');
            Route::get('/form/{uuid}', 'pessoaFisicaFormEditar');

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

            Route::prefix('terceiro')->group(function () {

                Route::get('', 'pessoaFisicaTerceiroIndex')->name('pessoa.pessoa-fisica.terceiro.index');
                Route::get('/form', 'pessoaFisicaTerceiroForm')->name('pessoa.pessoa-fisica.terceiro.form');
                Route::get('/form/{uuid}', 'pessoaFisicaTerceiroFormEditar');
            });

            Route::prefix('usuario')->group(function () {

                Route::get('', 'pessoaFisicaUsuarioIndex')->name('pessoa.pessoa-fisica.usuario.index');
                Route::get('/form', 'pessoaFisicaUsuarioForm')->name('pessoa.pessoa-fisica.usuario.form');
                Route::get('/form/{uuid}', 'pessoaFisicaUsuarioFormEditar');
            });
        });

        Route::prefix('pessoa-juridica')->group(function () {

            Route::prefix('cliente')->group(function () {

                Route::get('', 'pessoaJuridicaClienteIndex')->name('pessoa.pessoa-juridica.cliente.index');
                Route::get('/form', 'pessoaJuridicaClienteForm')->name('pessoa.pessoa-juridica.cliente.form');
                Route::get('/form/{uuid}', 'pessoaJuridicaClienteFormEditar');
            });

            Route::prefix('terceiro')->group(function () {

                Route::get('', 'pessoaJuridicaTerceiroIndex')->name('pessoa.pessoa-juridica.terceiro.index');
                Route::get('/form', 'pessoaJuridicaTerceiroForm')->name('pessoa.pessoa-juridica.terceiro.form');
                Route::get('/form/{uuid}', 'pessoaJuridicaTerceiroFormEditar');
            });
        });
    });
});
