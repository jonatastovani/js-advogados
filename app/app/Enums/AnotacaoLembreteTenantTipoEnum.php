<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum AnotacaoLembreteTenantTipoEnum: string
{
    use EnumTrait;

    case ANOTACAO = 'anotacao';
    case LEMBRETE = 'lembrete';
}
