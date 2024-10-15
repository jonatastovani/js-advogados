<?php

namespace App\Enums;

enum PermissionModulesEnum: int
{
    case ADMINISTRADOR = 1;
    case ADVOCACIA = 2;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::ADMINISTRADOR => [
                'id' => self::ADMINISTRADOR,
                'nome' => 'Módulo Administrador',
                'descricao' => 'Administração dos Módulos',
                'slug' => '/admin',
                'tenant_id' => 'admin',
                'tipo_modulo' => 'tenant',
            ],
            self::ADVOCACIA => [
                'id' => self::ADVOCACIA,
                'nome' => 'Módulo Advocacia',
                'descricao' => 'Sistema de Gerenciamento de Escritório de Advocacia',
                'slug' => '/adv',
                'tenant_type_id' => 3,
                'tipo_modulo' => 'tenant_type',
            ],
        };
    }
}
