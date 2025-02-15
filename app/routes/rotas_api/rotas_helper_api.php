<?php

use App\Http\Controllers\Comum\CepController;
use App\Http\Controllers\Referencias\PagamentoTipoController;
use App\Http\Controllers\Validacao\ValidacaoController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'helper',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,financeiro',
        'usuario.tenant',
    ],
], function () {

    Route::group([
        'prefix' => 'pagamento-tipo',
        'controller' => PagamentoTipoController::class,
    ], function () {

        Route::post('pagamento-unico/render', 'renderPagamentoUnico');
        Route::post('entrada-com-parcelamento/render', 'renderEntradaComParcelamento');
        Route::post('parcelado/render', 'renderParcelado');
        Route::post('recorrente/render', 'renderRecorrente');
        Route::post('condicionado/render', 'renderCondicionado');
    });

    Route::group([
        'prefix' => 'validacao',
        'controller' => ValidacaoController::class,
    ], function () {

        Route::prefix('documento')->group(function () {

            Route::post('cpf', 'CPFValidacao')->name('api.helper.validacao.documento.cpf');
            Route::post('cnpj', 'CNPJValidacao')->name('api.helper.validacao.documento.cnpj');
        });
    });

    Route::group([
        'prefix' => 'cep',
        'controller' => CepController::class,
    ], function () {
        Route::get('', function () {})->name('api.helper.cep');
        Route::get('{cep}', 'show');
    });
});
