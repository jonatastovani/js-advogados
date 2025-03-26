<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PagamentoStatusTipoEnum: int
{
    use EnumTrait;

    case ATIVO = 1;
    case ATIVO_EM_ANALISE = 2;
    case CANCELADO = 3;
    case CANCELADO_EM_ANALISE = 4;
    case LIQUIDADO = 5;
    case LIQUIDADO_EM_ANALISE = 6;

    public function detalhes(): array
    {
        return match ($this) {
            self::ATIVO => [
                'id' => self::ATIVO->value,
                'nome' => 'Ativo',
                'descricao' => 'O pagamento está ativo e com lançamentos vigentes.',
            ],
            self::ATIVO_EM_ANALISE => [
                'id' => self::ATIVO_EM_ANALISE->value,
                'nome' => 'Ativo (em análise)',
                'descricao' => 'O pagamento foi lançado, mas ainda não foi confirmado.',
            ],
            self::CANCELADO => [
                'id' => self::CANCELADO->value,
                'nome' => 'Cancelado',
                'descricao' => 'O pagamento foi cancelado.',
            ],
            self::CANCELADO_EM_ANALISE => [
                'id' => self::CANCELADO_EM_ANALISE->value,
                'nome' => 'Cancelado (em análise)',
                'descricao' => 'O pagamento foi cancelado, mas ainda não foi confirmado.',
            ],
            self::LIQUIDADO => [
                'id' => self::LIQUIDADO->value,
                'nome' => 'Liquidado',
                'descricao' => 'O pagamento foi totalmente quitado.',
            ],
            self::LIQUIDADO_EM_ANALISE => [
                'id' => self::LIQUIDADO_EM_ANALISE->value,
                'nome' => 'Liquidado (em análise)',
                'descricao' => 'O pagamento foi liquidado, mas ainda não foi confirmado.',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::ATIVO_EM_ANALISE->value;
    }

    static public function statusPagamentoTachado(): array
    {
        return [
            self::CANCELADO->value,
        ];
    }

    /**
     * Status que serão permitidos setar para novos registros de Pagamentos.
     */
    static private function statusPermitidoParaNovosPagamentos(): array
    {
        return [
            self::ATIVO->value,
            self::ATIVO_EM_ANALISE->value,
        ];
    }

    /**
     * Retorna os status que serão exibidos disponibilizados para novos registros de Pagamentos de Serviços.
     */
    static public function statusParaNovosPagamentosServicos(): array
    {
        $permitidos = self::statusPermitidoParaNovosPagamentos();

        return array_values(array_filter(
            self::staticDetailsToArray(),
            fn($detalhe) => in_array($detalhe['id'], $permitidos)
        ));
    }

    /**
     * Status que serão permitidos setar para novos registros de Pagamentos.
     */
    static private function statusPermitidoParaPagamentos(): array
    {
        return [
            self::ATIVO->value,
            self::ATIVO_EM_ANALISE->value,
            self::CANCELADO->value,
            self::CANCELADO_EM_ANALISE->value,
        ];
    }

    /**
     * Retorna os status que serão exibidos disponibilizados para novos registros de Pagamentos de Serviços.
     */
    static public function statusParaPagamentosServicosExistentes(): array
    {
        $permitidos = self::statusPermitidoParaPagamentos();

        return array_values(array_filter(
            self::staticDetailsToArray(),
            fn($detalhe) => in_array($detalhe['id'], $permitidos)
        ));
    }
}
