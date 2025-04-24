<?php

namespace App\Enums;

use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Traits\EnumTrait;

enum ParticipacaoReferenciaTipoEnum: string
{
    use EnumTrait;

    case SERVICO = Servico::class;
    case PAGAMENTO = ServicoPagamento::class;
    case LANCAMENTO = ServicoPagamentoLancamento::class;

    public static function participacaoReferenciaTipoParaServicosEDependentes(): array
    {
        return array_column(self::cases(), 'value');
    }
}
