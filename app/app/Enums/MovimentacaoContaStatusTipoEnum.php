<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum  MovimentacaoContaStatusTipoEnum: int
{
    use EnumTrait;

    case ATIVA = 1;
    case CANCELADA = 2;
    case BLOQUEADA = 3;

    public function detalhes(): array
    {
        return match ($this) {
            self::ATIVA => [
                'id' => self::ATIVA->value,
                'nome' => 'Ativa',
            ],
            self::CANCELADA => [
                'id' => self::CANCELADA->value,
                'nome' => 'Cancelada',
            ],
            self::BLOQUEADA => [
                'id' => self::BLOQUEADA->value,
                'nome' => 'Bloqueada',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::ATIVA->value;
    }
}
