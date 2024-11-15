<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ContaStatusTipoEnum: int
{
    use EnumTrait;

    case ATIVA = 1;
    case INATIVA = 2;
    case FECHADA = 3;
    case BLOQUEADA = 4;
    case PENDENTE = 5;

    public function detalhes(): array
    {
        return match ($this) {
            self::ATIVA => [
                'id' => self::ATIVA->value,
                'nome' => 'Ativa',
            ],
            self::INATIVA => [
                'id' => self::INATIVA->value,
                'nome' => 'Inativa',
            ],
            self::FECHADA => [
                'id' => self::FECHADA->value,
                'nome' => 'Fechada',
            ],
            self::BLOQUEADA => [
                'id' => self::BLOQUEADA->value,
                'nome' => 'Bloqueada',
            ],
            self::PENDENTE => [
                'id' => self::PENDENTE->value,
                'nome' => 'Pendente',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::ATIVA->value;
    }
}
