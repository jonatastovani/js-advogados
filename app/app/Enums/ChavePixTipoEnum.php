<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ChavePixTipoEnum: int
{
    use EnumTrait;

    case CPF = 1;
    case CNPJ = 2;
    case TELEFONE = 3;
    case EMAIL = 4;
    case CHAVE_ALEATORIA = 5;

    public function detalhes(): array
    {
        return match ($this) {
            self::CPF => [
                'id' => self::CPF->value,
                'nome' => 'CPF',
                'descricao' => 'Chave Pix do tipo CPF.',
            ],
            self::CNPJ => [
                'id' => self::CNPJ->value,
                'nome' => 'CNPJ',
                'descricao' => 'Chave Pix do tipo CNPJ.',
            ],
            self::TELEFONE => [
                'id' => self::TELEFONE->value,
                'nome' => 'Telefone',
                'descricao' => 'Chave Pix do tipo telefone.',
            ],
            self::EMAIL => [
                'id' => self::EMAIL->value,
                'nome' => 'Email',
                'descricao' => 'Chave Pix do tipo email.',
            ],
            self::CHAVE_ALEATORIA => [
                'id' => self::CHAVE_ALEATORIA->value,
                'nome' => 'Chave Aleatória',
                'descricao' => 'Chave Pix do tipo chave aleatória.',
            ],
        };
    }
}
