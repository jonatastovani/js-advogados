<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'tenant',
    'middleware' => [
        // 'tenant.rota.tipo:4,true,advocacia',
        'usuario.tenant',
    ],
], function () {

    Route::controller(App\Http\Controllers\Auth\TenantController::class)->group(function () {
        Route::get('', 'index')->name('api.tenant');

        Route::get('current', 'current');
        Route::get('{id}', 'show');
        Route::put('update-cliente/current', 'updateCliente');
    });

    Route::prefix('area-juridica')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\AreaJuridicaTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.area-juridica');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
        });
    });

    Route::prefix('conta')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\ContaTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::get('painel-conta', 'indexPainelConta');
            Route::post('', 'store')->name('api.tenant.conta');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('documento-tipo-tenant')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\DocumentoTipoTenantController::class)->group(function () {

            // Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::post('pessoa-tipo-aplicavel', 'indexPorPessoaTipoAplicavel');
            Route::post('', 'store')->name('api.tenant.documento-tipo-tenant');
            Route::match(['get', 'post'], '{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('domains')->group(function () {

        Route::controller(App\Http\Controllers\Auth\DomainController::class)->group(function () {
            Route::get('', 'index')->name('api.tenant.domains');
            Route::get('{id}', 'show');
        });
    });

    Route::prefix('escolaridade')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\EscolaridadeTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.escolaridade');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
        });
    });

    Route::prefix('estado-civil')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\EstadoCivilTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.estado-civil');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
        });
    });

    Route::prefix('forma-pagamento')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\FormaPagamentoTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.forma-pagamento');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('lancamento-categoria-tipo-tenant')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\LancamentoCategoriaTipoTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.lancamento-categoria-tipo-tenant');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('pagamento-tipo-tenant')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\PagamentoTipoTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.pagamento-tipo-tenant');
            Route::match(['get', 'post'], '{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::delete('{uuid}', 'destroy');
        });
    });

    Route::prefix('participacao-tipo-tenant')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\ParticipacaoTipoTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::post('index-configuracao-tipo', 'index');
            Route::post('', 'store')->name('api.tenant.participacao-tipo-tenant');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
            Route::get('empresa-geral', 'getParticipacaoEmpresaLancamentoGeral');
        });
    });

    Route::prefix('sexo')->group(function () {

        Route::controller(App\Http\Controllers\Tenant\SexoTenantController::class)->group(function () {

            Route::post('consulta-filtros', 'postConsultaFiltros');

            Route::get('', 'index');
            Route::post('', 'store')->name('api.tenant.sexo');
            Route::get('{uuid}', 'show');
            Route::put('{uuid}', 'update');
        });
    });
});
