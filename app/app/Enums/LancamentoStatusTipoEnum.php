<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LancamentoStatusTipoEnum: int
{
    use EnumTrait;

    case AGUARDANDO_PAGAMENTO = 1;
    case LIQUIDADO_EM_ANALISE = 2;
    case LIQUIDADO = 3;
    case LIQUIDADO_PARCIALMENTE_EM_ANALISE = 4;
    case LIQUIDADO_PARCIALMENTE = 5;
    case INADIMPLENTE_EM_ANALISE = 6;
    case INADIMPLENTE = 7;
    case REAGENDADO_EM_ANALISE = 8;
    case REAGENDADO = 9;
    case CANCELADO_EM_ANALISE = 10;
    case CANCELADO = 11;

    public function detalhes(): array
    {
        return match ($this) {
            self::AGUARDANDO_PAGAMENTO => [
                'id' => self::AGUARDANDO_PAGAMENTO->value,
                'nome' => 'Aguardando pagamento',
                'descricao' => 'O pagamento ainda não foi realizado e está aguardando.',
            ],
            self::LIQUIDADO_EM_ANALISE => [
                'id' => self::LIQUIDADO_EM_ANALISE->value,
                'nome' => 'Liquidado em análise',
                'descricao' => 'O lançamento foi alterado para liquidado, mas ainda não foi confirmado.',
            ],
            self::LIQUIDADO => [
                'id' => self::LIQUIDADO->value,
                'nome' => 'Liquidado',
                'descricao' => 'O pagamento foi totalmente quitado.',
            ],
            self::LIQUIDADO_PARCIALMENTE_EM_ANALISE => [
                'id' => self::LIQUIDADO_PARCIALMENTE_EM_ANALISE->value,
                'nome' => 'Liquidado parcialmente em análise',
                'descricao' => 'O lançamento foi alterado para liquidado parcialmente, mas ainda não foi confirmado.',
            ],
            self::LIQUIDADO_PARCIALMENTE => [
                'id' => self::LIQUIDADO_PARCIALMENTE->value,
                'nome' => 'Liquidado parcialmente',
                'descricao' => 'Apenas uma parte do valor foi pago, e o saldo ainda está pendente. Será gerado um novo lançamento para o saldo restante.',
            ],
            self::INADIMPLENTE_EM_ANALISE => [
                'id' => self::INADIMPLENTE_EM_ANALISE->value,
                'nome' => 'Inadimplente em análise',
                'descricao' => 'O lançamento foi alterado para inadimplente, mas ainda não foi confirmado.',
            ],
            self::INADIMPLENTE => [
                'id' => self::INADIMPLENTE->value,
                'nome' => 'Inadimplente',
                'descricao' => 'O prazo de pagamento foi excedido e o lançamento está em atraso.',
            ],
            self::REAGENDADO_EM_ANALISE => [
                'id' => self::REAGENDADO_EM_ANALISE->value,
                'nome' => 'Reagendado em análise',
                'descricao' => 'O lançamento foi alterado para reagendado, mas ainda não foi confirmado.',
            ],
            self::REAGENDADO => [
                'id' => self::REAGENDADO->value,
                'nome' => 'Reagendado',
                'descricao' => 'O lançamento foi reagendado para outra data.',
            ],
            self::CANCELADO_EM_ANALISE => [
                'id' => self::CANCELADO_EM_ANALISE->value,
                'nome' => 'Cancelado em análise',
                'descricao' => 'O lançamento foi alterado para cancelado, mas ainda não foi confirmado.',
            ],
            self::CANCELADO => [
                'id' => self::CANCELADO->value,
                'nome' => 'Cancelado',
                'descricao' => 'O pagamento foi cancelado ou o contrato foi encerrado.',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::AGUARDANDO_PAGAMENTO->value;
    }
}
