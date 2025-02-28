<?php

use Illuminate\Support\Facades\Route;

Route::prefix('documento')->group(function () {

    Route::prefix('documento-gerado')->group(function () {

        Route::controller(App\Http\Controllers\View\Documento\DocumentoGeradoController::class)->group(function () {

            Route::get('', function () {})->name('documento-gerado.impressao');
            Route::get('{uuid}', 'documentoGeradoImpressao');
        });
    });

    Route::controller(App\Http\Controllers\View\Documento\DocumentoController::class)->group(function () {

        Route::prefix('modelo')->group(function () {

            Route::get('', 'documentoModeloIndex')->name('documento.modelo.index');
            Route::get('{id}/form', 'documentoModeloForm');
            Route::get('{id}/form/{uuid}', 'documentoModeloFormEditar');
        });
    });
});
