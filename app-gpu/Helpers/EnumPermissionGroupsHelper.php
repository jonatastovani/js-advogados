<?php

namespace App\Helpers;

enum EnumPermissionGroupsHelper: int
{
    case ADMINISTRADOR = 1;
    case SUPORTE = 2;
    case ADMINISTRACAO_MODULO_INTELIGENCIA = 3;
    case INFORMACAO_SUBJETIVA = 4;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::ADMINISTRADOR => [
                'id' => self::ADMINISTRADOR,
                'nome' => 'Administração do Sistema',
                'descricao' => 'Grupo de permissões para administrar o sistema.',
                'modulo_id' => EnumPermissionModulesHelper::ADMINISTRADOR,
                'individuais' => true,
            ],
            self::SUPORTE => [
                'id' => self::SUPORTE,
                'nome' => 'Suporte do Sistema',
                'descricao' => 'Grupo de permissões para suporte do sistema.',
                'modulo_id' => EnumPermissionModulesHelper::ADMINISTRADOR,
                'grupo_pai_id' => self::ADMINISTRADOR,
                'individuais' => true,
            ],
            self::ADMINISTRACAO_MODULO_INTELIGENCIA => [
                'id' => self::ADMINISTRACAO_MODULO_INTELIGENCIA,
                'nome' => 'Administração Módulo Inteligência',
                'descricao' => 'Grupo de Permissões para Administração do Módulo de Inteligência',
                'modulo_id' => 4,
                'individuais' => true,
            ],
            self::INFORMACAO_SUBJETIVA => [
                'id' => self::INFORMACAO_SUBJETIVA,
                'nome' => 'Informação subjetiva',
                'descricao' => 'Grupo de Permissões relacionadas a sessão de Informação subjetiva',
                'modulo_id' => 4,
                'grupo_pai_id' => 4,
            ],
        };
    }
}
