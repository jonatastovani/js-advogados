<?php

namespace App\Traits;

use App\Helpers\UUIDsHelpers;
use Illuminate\Support\Facades\Auth;

trait AuthFuncoesControllerTrait
{

    /**
     * Realiza o login do administrador usando o ID.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function loginAdmin()
    {
        // ID do administrador no banco de dados
        $adminId = UUIDsHelpers::getAdmin();

        // Faz login usando o ID do administrador
        $admin = Auth::loginUsingId($adminId);

        // Retorna o administrador autenticado ou null se falhar
        return $admin;
    }
}
