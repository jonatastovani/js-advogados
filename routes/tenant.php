<?php

declare(strict_types=1);

use App\Http\Controllers\View\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\AddIdentifierRequestMiddleware;
use App\Http\Middleware\ClearModalSessionMiddleware;
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
    InitializeTenancyByDomain::class,
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
        'rs' => '[0-9]+',
    ]);
    Route::get('', function () {})->middleware("auth:sanctum");

    #Rotas para autenticação de usuários
    Route::controller(LoginController::class)->group(function () {

        Route::get('login', 'index')->name('login');
        Route::post('login', 'session_start')->name('login.post');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('', 'lobby');
            Route::get('lobby', 'lobby')->name('lobby');
            Route::get('logout', 'logout')->name('logout');
        });
    });


    Route::middleware([
        'auth:sanctum',
        'usuario.tenant',
    ])->group(function () {

        Route::prefix('admin')->group(function () {

            require __DIR__ . '/modulos/rotas_admin.php';
        });

        Route::prefix('adv')->group(function () {

            require __DIR__ . '/modulos/rotas_servico.php';
        });
    });


    // Route::get('test', function () {
    //     return view('test');
    // });

    require __DIR__ . '/rotas_api.php';
});
