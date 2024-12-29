<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovimentacaoContaTipoEnum: int
{
    use EnumTrait;

    case CREDITO = 1;
    case DEBITO = 2;
    case TRANSFERENCIA_ENTRE_CONTAS_CREDITO = 3;
    case TRANSFERENCIA_ENTRE_CONTAS_DEBITO = 4;
    case AJUSTE_CREDITO = 5;
    case AJUSTE_DEBITO = 6;

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
            self::TRANSFERENCIA_ENTRE_CONTAS_CREDITO => [
                'id' => self::TRANSFERENCIA_ENTRE_CONTAS_CREDITO->value,
                'nome' => 'Transferência entre contas - Crédito',
            ],
            self::TRANSFERENCIA_ENTRE_CONTAS_DEBITO => [
                'id' => self::TRANSFERENCIA_ENTRE_CONTAS_DEBITO->value,
                'nome' => 'Transferência entre contas - Debito',
            ],
            self::AJUSTE_CREDITO => [
                'id' => self::AJUSTE_CREDITO->value,
                'nome' => 'Ajuste - Crédito',
            ],
            self::AJUSTE_DEBITO => [
                'id' => self::AJUSTE_DEBITO->value,
                'nome' => 'Ajuste - Debito',
            ],
        };
    }

    static public function tiposMovimentacaoParaLancamentos(): array
    {
        return [
            self::CREDITO->detalhes(),
            self::DEBITO->detalhes(),
        ];
    }

    // /**
    //  * Retorna o tipo de movimentação contrária a partir do tipo de movimentação informado.
    //  *
    //  * @param int $id O ID do tipo de movimentação.
    //  *
    //  * @return int O tipo de movimentação contrária.
    //  */
    // static public function tipoMovimentacaoContraria($id): int
    // {
    //     return match ($id) {
    //         self::CREDITO->value => self::DEBITO->value,
    //         self::DEBITO->value => self::CREDITO->value,
    //     };
    // }
}
