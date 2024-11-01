<?php

namespace App\Enums;

use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;

enum PessoaTipoEnum: string
{
    case PESSOA_FISICA = PessoaFisica::class;
    case PESSOA_JURIDICA = PessoaJuridica::class;

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
