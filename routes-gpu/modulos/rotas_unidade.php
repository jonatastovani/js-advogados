<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'unidade/{tenant}',
    'middleware' => [
        'tenant.rota.tipo:3,true',
        'usuario.tenant',
    ],
], function () {
    Route::get('/', function () {
        return view('layouts.layout', ['dados' => ['/ tipo 5']]);
    });
    Route::get('/teste', function () {
        return view('layouts.layout', ['dados' => ['/teste tipo 5']]);
    });
});
