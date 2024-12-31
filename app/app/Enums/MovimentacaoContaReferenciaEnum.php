<?php

namespace App\Enums;

use App\Models\Documento\DocumentoGerado;
use App\Models\Financeiro\LancamentoGeral;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Tenant\ContaTenant;
use App\Traits\EnumTrait;

enum MovimentacaoContaReferenciaEnum: string
{
    use EnumTrait;

    case SERVICO_LANCAMENTO = ServicoPagamentoLancamento::class;
    case CONTA = ContaTenant::class;
    case DOCUMENTO_GERADO = DocumentoGerado::class;
    case LANCAMENTO_GERAL = LancamentoGeral::class;
}
