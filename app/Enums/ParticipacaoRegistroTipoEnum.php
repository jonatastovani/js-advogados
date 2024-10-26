<?php

namespace App\Enums;

enum ParticipacaoRegistroTipoEnum: int
{
    case PERFIL = 1;
    case GRUPO = 2;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::PERFIL => [
                'id' => self::PERFIL,
                'nome' => 'Perfil',
                'descricao' => 'Perfil de pessoa cadastrada CNPJ ou CPF.',
            ],
            self::GRUPO => [
                'id' => self::GRUPO,
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
