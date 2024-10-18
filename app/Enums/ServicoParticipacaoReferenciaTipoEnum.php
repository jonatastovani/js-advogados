<?php

namespace App\Enums;

use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;

enum ServicoParticipacaoReferenciaTipoEnum: int
{
    case PAGAMENTO = 1;
    case LANCAMENTO = 2;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::PAGAMENTO => [
                'id' => self::PAGAMENTO,
                'nome' => 'Pagamento',
                'descricao' => 'Registro da tabela de pagamentos',
                'tabela_ref' => ServicoPagamento::getTableName(),
                'tabela_model' => ServicoPagamento::class,
            ],
            self::LANCAMENTO => [
                'id' => self::LANCAMENTO,
                'nome' => 'Lançamento',
                'descricao' => 'Registro da tabela de lançamentos',
                'tabela_ref' => ServicoPagamentoLancamento::getTableName(),
                'tabela_model' => ServicoPagamentoLancamento::class,
            ],
        };
    }
}
