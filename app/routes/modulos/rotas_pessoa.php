<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pessoa')->group(function () {

    Route::controller(App\Http\Controllers\View\Pessoa\PessoaController::class)->group(function () {

        Route::prefix('pessoa-fisica')->group(function () {

            Route::get('', 'pessoaFisicaIndex')->name('pessoa.pessoa-fisica.index');
            Route::get('/form', 'pessoaFisicaForm')->name('pessoa.pessoa-fisica.form');
            Route::get('/form/{uuid}', 'pessoaFisicaFormEditar');
        });

        Route::prefix('pessoa-juridica')->group(function () {

            Route::get('', 'pessoaJuridicaIndex')->name('pessoa.pessoa-juridica.index');
            Route::get('/form', 'pessoaJuridicaForm')->name('pessoa.pessoa-juridica.form');
            Route::get('/form/{uuid}', 'pessoaJuridicaFormEditar');
        });
    });
});
