<?php

namespace App\Common;

use App\Models\RefPermissaoConfig;
use App\Models\User;

class PermissaoService
{
    /**
     * Verifica se o usuário possui recursivamente alguma das permissões especificadas ou suas permissões pai.
     *
     * @param \App\Models\User $user O usuário para verificar as permissões.
     * @param array $permissaoIds Um array contendo IDs das permissões a serem verificadas.
     * @return bool Retorna true se o usuário tem alguma das permissões, caso contrário, retorna false.
     */
    public static function temPermissaoRecursivaAcima(User $user, array $permissaoIds)
    {
        // Verifica se o usuário tem alguma das permissões diretamente
        if ($user->permissoes->pluck('permissao_id')->intersect($permissaoIds)->isNotEmpty()) {
            return true;
        }

        // Verifica se o usuário tem a permissão através de permissões escalonadas
        $permissoesPai = RefPermissaoConfig::whereIn('permissao_id', $permissaoIds)
            ->pluck('permissao_pai_id')
            ->toArray();

        if (!empty($permissoesPai)) {
            // Realiza uma busca recursiva para verificar as permissões pai
            return self::temPermissaoRecursivaAcima($user, $permissoesPai);
        }

        return true;
        // return false;
    }
    
    // public static function temPermissao(User $user, $permissaoId)
    // {
    //     // Verifica se o usuário tem a permissão diretamente
    //     if ($user->permissoes->pluck('permissao_id')->contains($permissaoId)) {
    //         return true;
    //     }

    //     // Verifica se o usuário tem a permissão através de permissões escalonadas
    //     $permissao = RefPermissao::find($permissaoId);

    //     if ($permissao && $permissao->permissao_pai_id) {
    //         // Realiza uma busca recursiva para verificar as permissões pai
    //         return self::temPermissao($user, $permissao->permissao_pai_id);
    //     }

    //     return false;
    // }

    // public static function temPermissaoRecursivaAcima(User $user, $permissaoId)
    // {
    //     // Verifica se o usuário tem a permissão diretamente
    //     if ($user->permissoes->pluck('permissao_id')->contains($permissaoId)) {
    //         return true;
    //     }

    //     // Verifica se o usuário tem a permissão através de permissões escalonadas
    //     $permissao = RefPermissao::find($permissaoId);

    //     if ($permissao && $permissao->permissao_pai_id) {
    //         // Realiza uma busca recursiva para verificar as permissões pai
    //         return self::temPermissaoRecursivaAcima($user, $permissao->permissao_pai_id);
    //     }

    //     return false;
    // }

}
