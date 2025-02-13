<?php

namespace App\Enums;

use App\Models\Financeiro\LancamentoAgendamento;
use App\Models\Financeiro\LancamentoGeral;
use App\Models\Financeiro\LancamentoRessarcimento;
use App\Traits\EnumTrait;

enum LancamentoTipoEnum: string
{
    use EnumTrait;

    case LANCAMENTO_AGENDAMENTO = LancamentoAgendamento::class;
    case LANCAMENTO_GERAL = LancamentoGeral::class;
    case LANCAMENTO_RESSARCIMENTO = LancamentoRessarcimento::class;
}
