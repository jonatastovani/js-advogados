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

    /**
     * Retorna um array com os valores dos enums de LancamentoTipo que permite
     * seus lançamentos serem liquidados com o status Liquidado Migração de Sistema.
     *
     * @return array
     */
    static public function lancamentoTipoQuePermiteLiquidadoMigracao(): array
    {
        return [
            self::LANCAMENTO_GERAL->value,
        ];
    }
}
