<?php

namespace App\Enums;

use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Tenant\ContaTenant;
use App\Traits\EnumTrait;

enum MovimentacaoContaReferenciaEnum: string
{
    use EnumTrait;

    case SERVICO_LANCAMENTO = ServicoPagamentoLancamento::class;
    case CONTA = ContaTenant::class;
}
