<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum TenantTypeEnum: int
{
    use EnumTrait;

    case ADMINISTRADOR = 1;
    case API = 2;
    case ADVOCACIA_AUTOMATICA = 3;
    case ADVOCACIA_MANUAL = 4;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::ADMINISTRADOR => [
                'id' => self::ADMINISTRADOR->value,
                'nome' => 'Administrador',
                'descricao' => 'Tenant Central de Administração do Sistema.',
            ],
            self::API => [
                'id' => self::API->value,
                'nome' => 'Api',
                'descricao' => 'Tenant somente para APIs.',
            ],
            self::ADVOCACIA_AUTOMATICA => [
                'id' => self::ADVOCACIA_AUTOMATICA->value,
                'nome' => 'Advocacia - Domínio Automático',
                'descricao' => 'Módulo Advocacia com identificação automática de domínio.',
            ],
            self::ADVOCACIA_MANUAL => [
                'id' => self::ADVOCACIA_MANUAL->value,
                'nome' => 'Advocacia - Domínio Manual',
                'descricao' => 'Módulo Advocacia com identificação manual de domínio.',
            ],
        };
    }
}
