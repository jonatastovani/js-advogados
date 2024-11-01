<?php

declare(strict_types=1);

use App\Http\Controllers\View\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\AddIdentifierRequestMiddleware;
use App\Http\Middleware\ClearModalSessionMiddleware;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

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
    AddIdentifierRequestMiddleware::class,
    ClearModalSessionMiddleware::class,
])->group(function () {

    Route::patterns([
        'id' => '[0-9]+',
        'uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
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

    require __DIR__ . '/modulos/rotas_admin.php';

    require __DIR__ . '/modulos/rotas_unidade.php';

    Route::prefix('modulo')->group(function () {
        require __DIR__ . '/modulos/rotas_inteligencia.php';
    });

    Route::get('test-redis', function () {
        $key = "redis-welcome-views";
        $views = 'null';
        try {
            $redis = Redis::connection('default');
            $redis->incr($key, 1);
            $views = $redis->get($key, null);
        } catch (\Throwable $th) {
            $views = $th->getMessage();
        }
        return view('test-redis')->with('views', $views);
    });

    Route::get('test', function () {
        return view('test');
    });
});
