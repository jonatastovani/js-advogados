<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum DocumentoGeradoTipoEnum: int
{
    use EnumTrait;

    case REPASSE_PARCEIRO = 1;

    public function detalhes(): array
    {
        return match ($this) {
            self::REPASSE_PARCEIRO => [
                'id' => self::REPASSE_PARCEIRO->value,
                'nome' => 'Repasse Parceiro',
            ],
        };
    }
}
