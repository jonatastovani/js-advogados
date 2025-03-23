<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LancamentoStatusTipoEnum: int
{
    use EnumTrait;

    case AGUARDANDO_PAGAMENTO_EM_ANALISE = 1;
    case AGUARDANDO_PAGAMENTO = 2;
    case LIQUIDADO_EM_ANALISE = 3;
    case LIQUIDADO = 4;
    case LIQUIDADO_PARCIALMENTE_EM_ANALISE = 5;
    case LIQUIDADO_PARCIALMENTE = 6;
    case INADIMPLENTE_EM_ANALISE = 7;
    case INADIMPLENTE = 8;
    case REAGENDADO_EM_ANALISE = 9;
    case REAGENDADO = 10;
    case CANCELADO_EM_ANALISE = 11;
    case CANCELADO = 12;
    case PAGAMENTO_CANCELADO_EM_ANALISE = 13;
    case PAGAMENTO_CANCELADO = 14;
    case LIQUIDADO_MIGRACAO_SISTEMA = 15;

    public function detalhes(): array
    {
        return match ($this) {
            self::AGUARDANDO_PAGAMENTO_EM_ANALISE => [
                'id' => self::AGUARDANDO_PAGAMENTO_EM_ANALISE->value,
                'nome' => 'Aguardando pagamento (em análise)',
                'descricao' => 'O pagamento foi lançado, mas ainda não foi confirmado.',
            ],
            self::AGUARDANDO_PAGAMENTO => [
                'id' => self::AGUARDANDO_PAGAMENTO->value,
                'nome' => 'Aguardando pagamento',
                'descricao' => 'O pagamento ainda não foi realizado e está aguardando.',
            ],
            self::LIQUIDADO_EM_ANALISE => [
                'id' => self::LIQUIDADO_EM_ANALISE->value,
                'nome' => 'Liquidado (em análise)',
                'descricao' => 'O lançamento foi alterado para liquidado, mas ainda não foi confirmado.',
            ],
            self::LIQUIDADO => [
                'id' => self::LIQUIDADO->value,
                'nome' => 'Liquidado',
                'descricao' => 'O pagamento foi totalmente quitado.',
                'campos_obrigatorios' => [
                    [
                        'nome' => 'data_recebimento',
                        'nome_exibir' => 'Data de recebimento',
                        'form_request_rule' => 'required|date',
                    ],
                ],
            ],
            self::LIQUIDADO_PARCIALMENTE_EM_ANALISE => [
                'id' => self::LIQUIDADO_PARCIALMENTE_EM_ANALISE->value,
                'nome' => 'Liquidado parcialmente (em análise)',
                'descricao' => 'O lançamento foi alterado para liquidado parcialmente, mas ainda não foi confirmado.',
            ],
            self::LIQUIDADO_PARCIALMENTE => [
                'id' => self::LIQUIDADO_PARCIALMENTE->value,
                'nome' => 'Liquidado parcialmente',
                'descricao' => 'Apenas uma parte do valor foi pago, e o saldo ainda está pendente. Será gerado um novo lançamento para o saldo restante.',
                'campos_obrigatorios' => [
                    [
                        'nome' => 'data_recebimento',
                        'nome_exibir' => 'Data de recebimento',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'valor_recebido',
                        'nome_exibir' => 'Valor recebido',
                        'form_request_rule' => 'required|numeric|min:0.01',
                    ],
                    [
                        'nome' => 'diluicao_data',
                        'nome_exibir' => 'Data diluição',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'diluicao_valor',
                        'nome_exibir' => 'Valor diluição',
                        'form_request_rule' => 'required|numeric|min:0.01',
                    ],
                ],
                'campos_opcionais' => [
                    [
                        'row_class_name' => 'rowDiluicao',
                        'parent_type' => 'array',
                        'parent_name' => 'diluicao_lancamento_adicionais',
                        'parent_form_request_rule' => 'nullable|array',
                        'children_class_name' => 'diluicao_adicionada',
                        'fields' => [
                            [
                                'nome' => 'diluicao_data',
                                'nome_exibir' => 'Data diluição',
                                'form_request_rule' => 'required|date',
                            ],
                            [
                                'nome' => 'diluicao_valor',
                                'nome_exibir' => 'Valor diluição',
                                'form_request_rule' => 'nullable|numeric|min:0.01',
                            ],
                        ]
                    ]
                ],
            ],
            self::INADIMPLENTE_EM_ANALISE => [
                'id' => self::INADIMPLENTE_EM_ANALISE->value,
                'nome' => 'Inadimplente (em análise)',
                'descricao' => 'O lançamento foi alterado para inadimplente, mas ainda não foi confirmado.',
            ],
            self::INADIMPLENTE => [
                'id' => self::INADIMPLENTE->value,
                'nome' => 'Inadimplente',
                'descricao' => 'O prazo de pagamento foi excedido e o lançamento está em atraso.',
            ],
            self::REAGENDADO_EM_ANALISE => [
                'id' => self::REAGENDADO_EM_ANALISE->value,
                'nome' => 'Reagendado (em análise)',
                'descricao' => 'O lançamento foi alterado para reagendado, mas ainda não foi confirmado.',
            ],
            self::REAGENDADO => [
                'id' => self::REAGENDADO->value,
                'nome' => 'Reagendado',
                'descricao' => 'O lançamento foi reagendado para outra data.',
            ],
            self::CANCELADO_EM_ANALISE => [
                'id' => self::CANCELADO_EM_ANALISE->value,
                'nome' => 'Cancelado (em análise)',
                'descricao' => 'O lançamento foi alterado para cancelado, mas ainda não foi confirmado.',
            ],
            self::CANCELADO => [
                'id' => self::CANCELADO->value,
                'nome' => 'Cancelado',
                'descricao' => 'O lançamento foi cancelado.',
            ],
            self::PAGAMENTO_CANCELADO_EM_ANALISE => [
                'id' => self::PAGAMENTO_CANCELADO_EM_ANALISE->value,
                'nome' => 'Pagamento Cancelado (em análise)',
                'descricao' => 'O pagamento foi cancelado, mas ainda não foi confirmado.',
            ],
            self::PAGAMENTO_CANCELADO => [
                'id' => self::PAGAMENTO_CANCELADO->value,
                'nome' => 'Pagamento Cancelado',
                'descricao' => 'O pagamento foi cancelado.',
            ],
            self::LIQUIDADO_MIGRACAO_SISTEMA => [
                'id' => self::LIQUIDADO_MIGRACAO_SISTEMA->value,
                'nome' => 'Liquidado (migração sistema)',
                'descricao' => 'O pagamento foi liquidado, mas sem movimentação de conta.',
            ],
        };
    }

    static public function statusPadraoSalvamentoServico(int $idPagamentoStatus): int
    {
        switch ($idPagamentoStatus) {
            case PagamentoStatusTipoEnum::ATIVO->value:
                return self::AGUARDANDO_PAGAMENTO->value;

            case PagamentoStatusTipoEnum::ATIVO_EM_ANALISE->value:
            default:
                return self::AGUARDANDO_PAGAMENTO_EM_ANALISE->value;
        }
    }

    static public function statusPadraoSalvamentoLancamentoGeral(): int
    {
        return self::AGUARDANDO_PAGAMENTO->value;
    }

    static public function statusPadraoSalvamentoLancamentoRessarcimento(): int
    {
        return self::AGUARDANDO_PAGAMENTO->value;
    }

    static public function statusPadraoLiquidadoParcialNovaDiluicao(): int
    {
        return self::AGUARDANDO_PAGAMENTO->value;
    }

    static public function statusImpossibilitaExclusao(): array
    {
        return [
            self::LIQUIDADO->value,
            self::LIQUIDADO_PARCIALMENTE->value,
            self::REAGENDADO->value,
            self::CANCELADO->value,
            self::LIQUIDADO_MIGRACAO_SISTEMA->value,
        ];
    }

    static public function statusImpossibilitaEdicaoLancamentoServico(): array
    {
        return [
            self::LIQUIDADO->value,
            self::LIQUIDADO_PARCIALMENTE->value,
            self::REAGENDADO->value,
            self::REAGENDADO_EM_ANALISE->value,
        ];
    }

    static public function statusComMovimentacaoConta(): array
    {
        $movimentacaoCredito = [
            'movimentacao_tipo_id' => MovimentacaoContaTipoEnum::CREDITO->value,
            'movimentacao_status_alterado_id' => MovimentacaoContaStatusTipoEnum::CANCELADA->value,
            'movimentacao_tipo_id_rollback' => MovimentacaoContaTipoEnum::DEBITO->value,
            'movimentacao_status_id_rollback' => MovimentacaoContaStatusTipoEnum::ROLLBACK->value,
        ];

        $movimentacaoDebito = [
            'movimentacao_tipo_id' => MovimentacaoContaTipoEnum::DEBITO->value,
            'movimentacao_status_alterado_id' => MovimentacaoContaStatusTipoEnum::CANCELADA->value,
            'movimentacao_tipo_id_rollback' => MovimentacaoContaTipoEnum::CREDITO->value,
            'movimentacao_status_id_rollback' => MovimentacaoContaStatusTipoEnum::ROLLBACK->value,
        ];

        return [
            [
                'status_id' => self::LIQUIDADO->value,
                ...$movimentacaoCredito,
            ],
            [
                'status_id' => self::LIQUIDADO_PARCIALMENTE->value,
                ...$movimentacaoCredito,
            ],
            [
                'status_id' => self::LIQUIDADO->value,
                ...$movimentacaoDebito,
            ],
        ];
    }

    static public function statusProbibidosEmLancamentosDiluidos(): array
    {
        return [
            self::LIQUIDADO_PARCIALMENTE->value,
        ];
    }

    static public function statusLancamentoTachado(): array
    {
        return [
            self::REAGENDADO->value,
            self::CANCELADO->value,
            self::PAGAMENTO_CANCELADO->value,
        ];
    }

    /**
     * Status que não serão permitidos atribuir, como status de reagendado e liquidado parcialmente.
     */
    static public function statusNaoPermitidoParaLancamentoGeral(): array
    {
        return [
            self::REAGENDADO_EM_ANALISE->value,
            self::REAGENDADO->value,
            self::LIQUIDADO_PARCIALMENTE_EM_ANALISE->value,
            self::LIQUIDADO_PARCIALMENTE->value,
            self::LIQUIDADO_MIGRACAO_SISTEMA->value,
        ];
    }

    /**
     * Retorna os status que serão exibidos nos filtros do front-end.
     */
    static public function statusParaFiltrosFrontEndLancamentoGeral(): array
    {
        $ocultos = self::statusNaoPermitidoParaLancamentoGeral();

        return array_values(array_filter(
            self::staticDetailsToArray(),
            fn($detalhe) => !in_array($detalhe['id'], $ocultos)
        ));
    }

    /**
     * Retorna os status que não poderão ser excluídos quando a execução do agendamento for resetada.
     */
    static public function statusNaoExcluirLancamentoGeralQuandoAgendamentoResetado(): array
    {
        return [
            self::LIQUIDADO->value,
            self::LIQUIDADO_PARCIALMENTE->value,
        ];
    }


    /**
     * Status que não serão permitidos atribuir, como status de reagendado e liquidado parcialmente.
     */
    static public function statusNaoPermitidoParaLancamentoRessarcimento(): array
    {
        return [
            self::REAGENDADO_EM_ANALISE->value,
            self::REAGENDADO->value,
            self::LIQUIDADO_PARCIALMENTE_EM_ANALISE->value,
            self::PAGAMENTO_CANCELADO_EM_ANALISE->value,
            self::PAGAMENTO_CANCELADO->value,
            self::CANCELADO_EM_ANALISE->value,
            self::LIQUIDADO_EM_ANALISE->value,
            self::INADIMPLENTE_EM_ANALISE->value,
            self::INADIMPLENTE->value,
            self::AGUARDANDO_PAGAMENTO_EM_ANALISE->value,
            self::LIQUIDADO_MIGRACAO_SISTEMA->value,
        ];
    }

    /**
     * Retorna os status que serão exibidos nos filtros do front-end.
     */
    static public function statusParaFiltrosFrontEndLancamentoRessarcimento(): array
    {
        $ocultos = self::statusNaoPermitidoParaLancamentoRessarcimento();

        return array_values(array_filter(
            self::staticDetailsToArray(),
            fn($detalhe) => !in_array($detalhe['id'], $ocultos)
        ));
    }

    static public function statusImpossibilitaEdicaoLancamentoRessarcimento(): array
    {
        return [
            self::LIQUIDADO->value,
            self::LIQUIDADO_PARCIALMENTE->value,
        ];
    }
}
