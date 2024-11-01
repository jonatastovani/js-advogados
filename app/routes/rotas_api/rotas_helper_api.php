<?php

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
    });

    Route::group([
        'prefix' => 'validacao',
        'controller' => ValidacaoController::class,
    ], function () {

        Route::prefix('documentos')->group(function () {

            Route::post('cpf', 'CPFValidacao');
            Route::post('cnpj', 'CNPJValidacao');
        });
    });
});
