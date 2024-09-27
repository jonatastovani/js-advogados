<?php

namespace App\Helpers;

use App\Models\Auth\Permission;
use App\Models\Auth\PermissionGroup;

class PermissionHelper
{
    /**
     * Verifica se há uma referência circular entre o grupo e seus grupos pais.
     *
     * @param int $grupoId O ID do grupo que está sendo salvo.
     * @param int|null $permissaoPaiId O ID do grupo pai, se houver.
     * @return bool Retorna true se houver referência circular, false caso contrário.
     */
    public static function verificaReferenciaCircularGrupoPai(int $grupoId, ?int $grupoPaiId): bool
    {
        // Se o grupo pai for null, não há referência circular
        if (is_null($grupoPaiId)) {
            return false;
        }

        // Inicia a verificação recursiva
        return self::checarReferenciaCircularGrupoPai($grupoId, $grupoPaiId);
    }

    /**
     * Função recursiva para verificar se há referência circular na árvore de grupos pais.
     *
     * @param int $grupoId O ID do grupo que está sendo salvo.
     * @param int $grupoPaiId O ID do grupo pai atual.
     * @return bool Retorna true se houver referência circular, false caso contrário.
     */
    private static function checarReferenciaCircularGrupoPai(int $grupoId, int $grupoPaiId): bool
    {
        // Busca o grupo pai no banco de dados
        $grupoPai = PermissionGroup::find($grupoPaiId);

        // // Se o grupo pai não existir, não há referência circular
        // if (!$grupoPai) {
        //     return false;
        // }

        // Se o grupo pai for o próprio grupo que está sendo salvo, há referência circular
        if ($grupoPai->id === $grupoId) {
            return true;
        }

        if($grupoPai->grupo_pai_id === null) {
            return false;
        }

        // Caso contrário, continue a busca recursiva com o grupo pai do grupo pai
        return self::checarReferenciaCircularGrupoPai($grupoId, $grupoPai->grupo_pai_id);
    }

    /**
     * Verifica se há uma referência circular entre o permissão e suas permissões pais.
     
     * @param int $permissaoId O ID da permissão que está sendo salva.
     * @param int|null $permissaoPaiId O ID do grupo pai, se houver.
     * @return bool Retorna true se houver referência circular, false caso contrário.
     */
    public static function verificaReferenciaCircularPermissaoPai(int $permissaoId, ?int $permissaoPaiId): bool
    {
        // Se a permissão pai for null, não há referência circular
        if (is_null($permissaoPaiId)) {
            return false;
        }

        // Inicia a verificação recursiva
        return self::checarReferenciaCircularPermissaoPai($permissaoId, $permissaoPaiId);
    }

    /**
     * Função recursiva para verificar se há referência circular na árvore de permissões pais.
     *
     * @param int $permissaoId O ID da permissão que está sendo salva.
     * @param int $permissaoPaiId O ID da permissão pai atual.
     * @return bool Retorna true se houver referência circular, false caso contrário.
     */
    private static function checarReferenciaCircularPermissaoPai(int $permissaoId, int $permissaoPaiId): bool
    {
        // Busca a permissão pai no banco de dados
        $permissaoPai = Permission::find($permissaoPaiId);

        // // Se o grupo pai não existir, não há referência circular
        // if (!$permissaoPai) {
        //     return false;
        // }

        // Se a permissão pai for a própria permissão que está sendo salva, há referência circular
        if ($permissaoPai->id === $permissaoId) {
            return true;
        }

        if($permissaoPai->permissao_pai_id === null) {
            return false;
        }

        // Caso contrário, continue a busca recursiva com a permissão pai da permissão pai
        return self::checarReferenciaCircularPermissaoPai($permissaoId, $permissaoPai->permissao_pai_id);
    }

}
