<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovimentacaoContaStatusTipoEnum: int
{
    use EnumTrait;

    case ATIVA = 1;
    case CANCELADA = 2;
    case EM_ALTERACAO = 3;
    case ROLLBACK = 4;
    case FINALIZADA = 5;

    public function detalhes(): array
    {
        return match ($this) {
            self::ATIVA => [
                'id' => self::ATIVA->value,
                'nome' => 'Ativa',
                'descricao' => 'Movimentação ativa e válida.',
            ],
            self::CANCELADA => [
                'id' => self::CANCELADA->value,
                'nome' => 'Cancelada',
                'descricao' => 'Movimentação cancelada.',
            ],
            self::EM_ALTERACAO => [
                'id' => self::EM_ALTERACAO->value,
                'nome' => 'Em Alteração',
                'descricao' => 'Movimentação em processo de alteração.',
            ],
            self::ROLLBACK => [
                'id' => self::ROLLBACK->value,
                'nome' => 'Rollback',
                'descricao' => 'Movimentação de ajuste para desfazer outra.',
            ],
            self::FINALIZADA => [
                'id' => self::FINALIZADA->value,
                'nome' => 'Finalizada',
                'descricao' => 'Movimentação finalizada e bloqueada para alterações.',
            ],
        };
    }

    static public function statusPadraoSalvamentoServicoLancamento(): int
    {
        return self::ATIVA->value;
    }

    static public function statusPermiteAlteracao(): array
    {
        return [
            self::ATIVA->value,
        ];
    }

    static public function statusPermiteRollback(): array
    {
        return [
            self::ATIVA->value,
        ];
    }

    static public function statusImpossibilitaAlteracao(): array
    {
        return [
            self::FINALIZADA->value,
            self::CANCELADA->value,
        ];
    }

    /**
     * Status que não serão mostrados nas consultas, como status de correção ou rollback.
     */
    static public function statusOcultoNasConsultas(): array
    {
        return [
            self::ROLLBACK->value,
            self::EM_ALTERACAO->value, // Está aqui porque ainda não achei utilidade para esse tipo
        ];
    }

    /**
     * Status que mostrarão participantes, caso a movimentação tenha participantes.
     */
    static public function statusServicoLancamentoComParticipantes(): array
    {
        return [
            self::ATIVA->value,
            self::FINALIZADA->value,
        ];
    }

    /**
     * Status que mostrarão participantes, caso a movimentação tenha participantes.
     */
    static public function statusMostrarBalancoRepasseParceiro(): array
    {
        return [
            self::ATIVA->value,
            self::FINALIZADA->value,
        ];
    }
}
