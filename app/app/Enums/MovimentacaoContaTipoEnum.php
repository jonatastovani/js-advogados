<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovimentacaoContaTipoEnum: int
{
    use EnumTrait;

    case CREDITO = 1;
    case DEBITO = 2;
    case AJUSTE = 3;
    case TRANSFERENCIA_ENTRE_CONTAS = 4;

    public function detalhes(): array
    {
        return match ($this) {
            self::CREDITO => [
                'id' => self::CREDITO->value,
                'nome' => 'Crédito',
            ],
            self::DEBITO => [
                'id' => self::DEBITO->value,
                'nome' => 'Débito',
            ],
            self::AJUSTE => [
                'id' => self::AJUSTE->value,
                'nome' => 'Ajuste',
            ],
            self::TRANSFERENCIA_ENTRE_CONTAS => [
                'id' => self::TRANSFERENCIA_ENTRE_CONTAS->value,
                'nome' => 'Transferência entre contas',
            ],
        };
    }

    static public function tiposMovimentacaoModalLancamentoGeral(): array
    {
        return [
            self::CREDITO->detalhes(),
            self::DEBITO->detalhes(),
        ];
    }
}
