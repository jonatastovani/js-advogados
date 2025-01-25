<?php

namespace App\Enums;

use App\Models\Financeiro\LancamentoGeral;
use App\Models\Financeiro\LancamentoRessarcimento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Traits\EnumTrait;

enum ParticipacaoTipoTenantConfiguracaoTipoEnum: string
{
    use EnumTrait;

    case LANCAMENTO_GERAL = LancamentoGeral::class;
    case LANCAMENTO_SERVICO = ServicoPagamentoLancamento::class;
    case LANCAMENTO_RESSARCIMENTO = LancamentoRessarcimento::class;
}
