<?php

namespace App\Enums;

use App\Models\Servico\ServicoPagamentoLancamento;
use App\Traits\EnumTrait;

enum MovimentacaoContaReferenciaEnum: string
{
    use EnumTrait;

    case SERVICO_LANCAMENTO = ServicoPagamentoLancamento::class;
}
