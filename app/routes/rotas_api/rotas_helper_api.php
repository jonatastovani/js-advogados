<?php

use App\Http\Middleware\ExistingUserTenantDomainMiddleware;
use App\Http\Controllers\Comum\CepController;
use App\Http\Controllers\Referencias\PagamentoTipoController;
use App\Http\Controllers\Tenant\DocumentoModeloTenantController;
use App\Http\Controllers\Validacao\ValidacaoController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'helper',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,financeiro',
        // 'usuario.tenant',
        ExistingUserTenantDomainMiddleware::class,
    ],
], function () {

    Route::group([
        'prefix' => 'cep',
        'controller' => CepController::class,
    ], function () {
        Route::get('', function () {})->name('api.helper.cep');
        Route::get('{cep}', 'show');
    });

    Route::group([
        'prefix' => 'documento-modelo-tenant',
        'controller' => DocumentoModeloTenantController::class,
    ], function () {
        Route::get('', function () {})->name('api.helper.documento-modelo-tenant');

        Route::post('{documento_modelo_tipo_id}', 'verificacaoDocumentoEmCriacao');
        Route::post('render-documento', 'verificacaoDocumentoRenderizar');
        Route::post('render-objetos', 'renderObjetos');
    });

    Route::group([
        'prefix' => 'pagamento-tipo',
        'controller' => PagamentoTipoController::class,
    ], function () {

        Route::post('pagamento-unico/render', 'renderPagamentoUnico');
        Route::post('entrada-com-parcelamento/render', 'renderEntradaComParcelamento');
        Route::post('parcelado/render', 'renderParcelado');
        Route::post('recorrente/render', 'renderRecorrente');
        Route::post('livre-incremental/render', 'renderLivreIncremental');
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
});
