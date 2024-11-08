<?php

namespace App\Enums;

use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Traits\EnumTrait;

enum PessoaTipoEnum: string
{
    use EnumTrait;

    case PESSOA_FISICA = PessoaFisica::class;
    case PESSOA_JURIDICA = PessoaJuridica::class;
}
