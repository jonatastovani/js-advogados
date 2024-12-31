<?php

use Illuminate\Support\Facades\Route;

Route::controller(App\Http\Controllers\View\Documento\DocumentoGeradoController::class)->group(function () {

    Route::prefix('documento-gerado')->group(function () {
        Route::get('', function () {})->name('documento-gerado.impressao');
        Route::get('{uuid}', 'documentoGeradoImpressao');
    });
});
