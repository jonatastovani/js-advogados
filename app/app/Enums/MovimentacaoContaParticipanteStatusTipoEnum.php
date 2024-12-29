<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovimentacaoContaParticipanteStatusTipoEnum: int
{
    use EnumTrait;

    case ATIVA = 1;
    case FINALIZADA = 2;

    public function detalhes(): array
    {
        return match ($this) {
            self::ATIVA => [
                'id' => self::ATIVA->value,
                'nome' => 'Ativa',
                'descricao' => 'Movimentação ativa e válida.',
            ],
            self::FINALIZADA => [
                'id' => self::FINALIZADA->value,
                'nome' => 'Finalizada',
                'descricao' => 'Movimentação finalizada e bloqueada para alterações.',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::ATIVA->value;
    }

    static public function statusPermiteAlteracao(): array
    {
        return [
            self::ATIVA->value,
        ];
    }

    static public function statusImpossibilitaAlteracao(): array
    {
        return [
            self::FINALIZADA->value,
        ];
    }

    /**
     * Registros que serão filtrados nos relatorios de balanço de repasse com parceiro.
     */
    static public function statusMostrarBalancoRepasseParceiro(): array
    {
        return [
            self::ATIVA->value,
            self::FINALIZADA->value,
        ];
    }
}
