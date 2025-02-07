<?php

namespace App\Enums;

use App\Models\Financeiro\LancamentoGeral;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Traits\EnumTrait;

enum TagTipoTenantEnum: string
{
    use EnumTrait;

    case LANCAMENTO_GERAL = 'lancamento_geral';
}
