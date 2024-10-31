<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ParticipacaoRegistroTipoEnum: int
{
    use EnumTrait;

    case PERFIL = 1;
    case GRUPO = 2;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::PERFIL => [
                'id' => self::PERFIL->value,
                'nome' => 'Perfil',
                'descricao' => 'Perfil de pessoa cadastrada CNPJ ou CPF.',
            ],
            self::GRUPO => [
                'id' => self::GRUPO->value,
                'nome' => 'Grupo',
                'descricao' => "Grupo de perfis de pessoas CNPJ ou CPF da Participação.",
            ],
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
