<?php

declare(strict_types=1);

use App\Http\Controllers\View\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\AddIdentifierRequestMiddleware;
use App\Http\Middleware\CheckManualInitializationTenantDomain;
use App\Http\Middleware\ClearModalSessionMiddleware;
use App\Http\Middleware\HandleTenantDomainForTenantType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    HandleTenantDomainForTenantType::class,
    InitializeTenancyByDomain::class,
    CheckManualInitializationTenantDomain::class,
    PreventAccessFromCentralDomains::class,
    AddIdentifierRequestMiddleware::class,
    ClearModalSessionMiddleware::class,
])->group(function () {

    Route::patterns([
        'id' => '[0-9]+',
        'uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
        'modulo_id' => '[0-9]+',
        'grupo_id' => '[0-9]+',
        'permissao_id' => '[0-9]+',
        'servico_uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
        'pagamento_uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
        'lancamento_uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
        'perfil_uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
        'documento_modelo_tipo_id' => '[0-9]+',
    ]);

    Auth::routes(
        [
            // 'verify' => true,
            'register' => false,
        ]
    );

    Route::get('', function () {})->middleware("auth:sanctum");

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::middleware([
        'auth:sanctum',
        'usuario.tenant',
    ])->group(function () {

        Route::prefix('admin')->group(function () {

            require __DIR__ . '/modulos/rotas_admin.php';
        });

        Route::prefix('adv')->group(function () {

            require __DIR__ . '/modulos/rotas_documento.php';
            require __DIR__ . '/modulos/rotas_financeiro.php';
            require __DIR__ . '/modulos/rotas_pessoa.php';
            require __DIR__ . '/modulos/rotas_servico.php';
            require __DIR__ . '/modulos/rotas_sistema.php';
        });
    });

    Route::get('', function () {
        return redirect(route('home'));
    });

    // Route::get('test', function () {
    //     return view('test');
    // });

    require __DIR__ . '/rotas_api.php';
});
