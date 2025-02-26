<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum DocumentoModeloTipoEnum: int
{
    use EnumTrait;

    case SERVICO = 1;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::SERVICO => [
                'id' => self::SERVICO->value,
                'nome' => 'Documento para Serviços',
            ],
        };
    }
}
