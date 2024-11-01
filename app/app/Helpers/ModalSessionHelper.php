<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class ModalSessionHelper
{
    public static function registerModal(string $modalName, string $requestUuid): bool
    {
        // Recupera os modais carregados da sessão
        $modalLoaded = session()->get('modals_loaded', []);

        // Verifica se o modal já foi carregado para o request atual
        if (isset($modalLoaded[$requestUuid]) && in_array($modalName, $modalLoaded[$requestUuid])) {
            return false; // Modal já foi carregado para essa requisição
        }

        // Registra o modal para o request atual
        $modalLoaded[$requestUuid][] = $modalName;
        session()->put('modals_loaded', $modalLoaded);

        return true; // Modal registrado com sucesso
    }
}
