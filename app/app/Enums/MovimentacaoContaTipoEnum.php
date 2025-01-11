<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovimentacaoContaTipoEnum: int
{
    use EnumTrait;

    case CREDITO = 1;
    case DEBITO = 2;
    case DEBITO_LIBERACAO_CREDITO = 3;
    case LIBERACAO_CREDITO = 4;
    case AJUSTE_SALDO = 5;
    // case TRANSFERENCIA_ENTRE_CONTAS_CREDITO = 3;
    // case TRANSFERENCIA_ENTRE_CONTAS_DEBITO = 4;
    // case AJUSTE_DEBITO = 6;

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
            self::DEBITO_LIBERACAO_CREDITO => [
                'id' => self::DEBITO_LIBERACAO_CREDITO->value,
                'nome' => 'Débito - Liberação de Crédito',
                'descricao' => 'Débito do valor da Movimentação de Crédito pertencente a Empresa na confirmação do repasse. Ação que antecede a Movimentação de Liberação de Crédito.',
            ],
            self::LIBERACAO_CREDITO => [
                'id' => self::LIBERACAO_CREDITO->value,
                'nome' => 'Liberação de Crédito',
                'descricao' => 'Liberação de valor da Movimentação de Crédito pertencente a Empresa no momento da confirmação do repasse.',
            ],
            self::AJUSTE_SALDO => [
                'id' => self::AJUSTE_SALDO->value,
                'nome' => 'Ajuste de Saldo',
            ],
            // self::TRANSFERENCIA_ENTRE_CONTAS_CREDITO => [
            //     'id' => self::TRANSFERENCIA_ENTRE_CONTAS_CREDITO->value,
            //     'nome' => 'Transferência entre contas - Crédito',
            // ],
            // self::TRANSFERENCIA_ENTRE_CONTAS_DEBITO => [
            //     'id' => self::TRANSFERENCIA_ENTRE_CONTAS_DEBITO->value,
            //     'nome' => 'Transferência entre contas - Debito',
            // ],
            // self::AJUSTE_DEBITO => [
            //     'id' => self::AJUSTE_DEBITO->value,
            //     'nome' => 'Ajuste - Debito',
            // ],
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
