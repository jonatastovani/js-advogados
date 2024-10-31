<?php

namespace App\Enums;

use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Traits\EnumTrait;

enum ServicoParticipacaoReferenciaTipoEnum: string
{
    use EnumTrait;

    case SERVICO = Servico::class;
    case PAGAMENTO = ServicoPagamento::class;
    case LANCAMENTO = ServicoPagamentoLancamento::class;
}
