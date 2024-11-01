<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ServicoPagamentoLancamentoStatusTipoEnum: int
{
    use EnumTrait;

    case AGUARDANDO_PAGAMENTO = 1;
    case INADIMPLENTE = 2;
    case LIQUIDADO_PARCIALMENTE = 3;
    case LANCADO_PARA_O_FINAL = 4;
    case LIQUIDADO = 5;
    case CANCELADO = 6;
    case EM_ANALISE = 7;
    case PAGAMENTO_REAGENDADO = 8;

    public function detalhes(): array
    {
        return match ($this) {
            self::AGUARDANDO_PAGAMENTO => [
                'id' => self::AGUARDANDO_PAGAMENTO->value,
                'nome' => 'Aguardando pagamento',
                'descricao' => 'O pagamento ainda não foi realizado e está aguardando.',
            ],
            self::INADIMPLENTE => [
                'id' => self::INADIMPLENTE->value,
                'nome' => 'Inadimplente',
                'descricao' => 'O prazo de pagamento foi excedido e a parcela está em atraso.',
            ],
            self::LIQUIDADO_PARCIALMENTE => [
                'id' => self::LIQUIDADO_PARCIALMENTE->value,
                'nome' => 'Liquidado parcialmente',
                'descricao' => 'Apenas uma parte do valor foi pago, e o saldo ainda está pendente. Será gerado um novo lançamento para o saldo restante.',
            ],
            self::LANCADO_PARA_O_FINAL => [
                'id' => self::LANCADO_PARA_O_FINAL->value,
                'nome' => 'Lançado para o final',
                'descricao' => 'O pagamento foi postergado e transferido para o final do cronograma de pagamento.',
            ],
            self::LIQUIDADO => [
                'id' => self::LIQUIDADO->value,
                'nome' => 'Liquidado',
                'descricao' => 'O pagamento foi totalmente quitado.',
            ],
            self::CANCELADO => [
                'id' => self::CANCELADO->value,
                'nome' => 'Cancelado',
                'descricao' => 'O pagamento foi cancelado ou o contrato foi encerrado.',
            ],
            self::EM_ANALISE => [
                'id' => self::EM_ANALISE->value,
                'nome' => 'Em análise',
                'descricao' => 'O pagamento foi realizado, mas está em processo de verificação.',
            ],
            self::PAGAMENTO_REAGENDADO => [
                'id' => self::PAGAMENTO_REAGENDADO->value,
                'nome' => 'Pagamento reagendado',
                'descricao' => 'O pagamento foi agendado para uma data futura.',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::AGUARDANDO_PAGAMENTO->value;
    }
}
