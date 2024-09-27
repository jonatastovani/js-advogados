<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(
    function () {

        Route::patterns([
            'id' => '[0-9]+',
            'uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
            'modulo_id' => '[0-9]+',
            'grupo_id' => '[0-9]+',
            'permissao_id' => '[0-9]+',
            'rs' => '[0-9]+',
        ]);

        Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
            Route::post('login-front', 'loginFrontApi');
            Route::post('login', 'session_start_post')->name('api.login.post');
        });

        // Rotas protegidas pelo sanctum e acrescentados dados da sessão no sanctum.verify em caso de acesso pelo front
        Route::middleware(['auth:sanctum', 'api'])->group(function () {
            
            Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
                Route::post('/check-token', 'checkTokenApi');
                Route::post('/logout', 'logoutApi');
                Route::post('/logout-list', 'logoutListApi');
            });

            require __DIR__ . '/rotas_api/rotas_admin_api.php';
            
            require __DIR__ . '/rotas_api/rotas_fotos_api.php';

            require __DIR__ . '/rotas_api/rotas_inteligencia_api.php';
            
            require __DIR__ . '/rotas_api/rotas_pessoas_api.php';

            // Route::get('foto-preso/{idPreso}', function () {
            //     $retorno = app(App\Helpers\FotoHelper::class)->buscarFotoPreso(request('idPreso'));
            //     $response = App\Common\RestResponse::createSuccessResponse($retorno, 200);
            //     return response()->json($response->toArray(), $response->getStatusCode());
            // });

        });
    }
);
