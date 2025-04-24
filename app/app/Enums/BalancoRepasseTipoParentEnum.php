<?php

namespace App\Enums;

use App\Models\Financeiro\LancamentoRessarcimento;
use App\Models\Financeiro\MovimentacaoConta;
use App\Traits\EnumTrait;

enum BalancoRepasseTipoParentEnum: string
{
    use EnumTrait;

    case MOVIMENTACAO_CONTA = MovimentacaoConta::class;
    case LANCAMENTO_RESSARCIMENTO = LancamentoRessarcimento::class;
}
