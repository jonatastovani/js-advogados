<?php

namespace App\Traits;

use App\Helpers\UUIDsHelpers;
use Illuminate\Support\Facades\Auth;

trait AuthControllerTrait
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

    /**
     * Verifica se o perfil do usuário está ativo.
     *
     * @param  \App\Models\Auth\User  $user
     * @return bool
     */
    protected function isPerfilUsuarioAtivo($user): bool
    {
        return $user->pessoa_perfil && $user->pessoa_perfil->ativo_bln;
    }

    /**
     * Retorna a mensagem de perfil inativo.
     *
     * @return string
     */
    protected function getPerfilInativoMessage(): string
    {
        return trans('auth.profile_inactive');
    }

    /**
     * Retorna a mensagem de usuário inativo.
     *
     * @return string
     */
    protected function getUsuarioInativoMessage(): string
    {
        return trans('auth.user_inactive');
    }
}
