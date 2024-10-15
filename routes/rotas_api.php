<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => 'api',
        'middleware' => [
            'api',
            'auth:sanctum',
        ]
    ],
    function () {

        require __DIR__ . '/rotas_api/rotas_admin_api.php';

        require __DIR__ . '/rotas_api/rotas_advocacia_api.php';

        require __DIR__ . '/rotas_api/rotas_financeiro_api.php';

        require __DIR__ . '/rotas_api/rotas_referencias_api.php';

        require __DIR__ . '/rotas_api/rotas_helper_api.php';
    }
);
