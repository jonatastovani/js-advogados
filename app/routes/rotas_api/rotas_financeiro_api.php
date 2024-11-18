<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'financeiro',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,financeiro',
        'usuario.tenant',
    ],
], function () {

    Route::prefix('conta')->group(function () {

        Route::controller(App\Http\Controllers\Financeiro\ContaController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.financeiro.conta');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('lancamentos')->group(function () {

        Route::get('', function () {})->name('api.financeiro.lancamentos');

        Route::prefix('servicos')->group(function () {

            Route::controller(App\Http\Controllers\Servico\ServicoPagamentoLancamentoController::class)->group(function () {

                Route::post('consulta-filtros', 'postConsultaFiltros');
                Route::post('{uuid}', 'show');
            });
        });
    });

    Route::prefix('movimentacao-conta/lancamentos')->group(function () {

        Route::controller(App\Http\Controllers\Financeiro\MovimentacaoContaController::class)->group(function () {

            Route::get('', function () {})->name('api.financeiro.movimentacao-conta.lancamentos');

            Route::prefix('servicos')->group(function () {
                Route::post('', 'storeLancamentoServico');
                Route::post('status-alterar', 'alterarStatusLancamentoServico');
            });
        });
    });

    Route::prefix('pagamento-tipo-tenant')->group(function () {

        Route::controller(App\Http\Controllers\Financeiro\PagamentoTipoTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.financeiro.pagamento-tipo-tenant');
            Route::match(['get', 'post'], '{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });
});
