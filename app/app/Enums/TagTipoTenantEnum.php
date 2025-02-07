<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum TagTipoTenantEnum: string
{
    use EnumTrait;

    case LANCAMENTO_GERAL = 'lancamento_geral';
}
