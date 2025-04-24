<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovimentacaoContaStatusTipoEnum: int
{
    use EnumTrait;

    case ATIVA = 1;
    case CANCELADA = 2;
    case FINALIZADA = 3;
    case ROLLBACK = 4;
    case EM_REPASSE_COMPENSACAO = 5;

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
            self::FINALIZADA => [
                'id' => self::FINALIZADA->value,
                'nome' => 'Finalizada',
                'descricao' => 'Movimentação finalizada e bloqueada para alterações.',
            ],
            self::ROLLBACK => [
                'id' => self::ROLLBACK->value,
                'nome' => 'Rollback',
                'descricao' => 'Movimentação de ajuste para desfazer outra.',
            ],
            self::EM_REPASSE_COMPENSACAO => [
                'id' => self::EM_REPASSE_COMPENSACAO->value,
                'nome' => 'Em Repasse/Compensação',
                'descricao' => 'Movimentação em repasse/compensação de valores.',
            ],
        };
    }

    static public function statusPadraoSalvamentoLancamentoServico(): int
    {
        return self::ATIVA->value;
    }

    static public function statusPadraoSalvamentoLancamentoGeral(): int
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
            self::EM_REPASSE_COMPENSACAO->value,
        ];
    }

    /**
     * Registros que serão filtrados nos relatorios de balanço de repasse com parceiro.
     */
    static public function statusMostrarBalancoRepasse(): array
    {
        return [
            self::ATIVA->value,
            self::FINALIZADA->value,
            self::EM_REPASSE_COMPENSACAO->value,
        ];
    }

    static public function statusMostrarBalancoRepasseFrontEnd(): array
    {
        $mostrar = self::statusMostrarBalancoRepasse();

        return array_values(array_filter(
            self::staticDetailsToArray(),
            fn($detalhe) => in_array($detalhe['id'], $mostrar)
        ));
    }

    /**
     * Retorna os status que serão exibidos nos filtros do front-end.
     */
    static public function statusParaFiltrosFrontEnd(): array
    {
        $ocultos = self::statusOcultoNasConsultas();

        return array_values(array_filter(
            self::staticDetailsToArray(),
            fn($detalhe) => !in_array($detalhe['id'], $ocultos)
        ));
    }
}
