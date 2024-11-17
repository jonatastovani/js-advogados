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
                'configuracao' => [
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'data_recebimento',
                            'nome_exibir' => 'Data de recebimento',
                            'form_request_rule' => 'required|date',
                        ],
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
                'configuracao' => [
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
                            'nome_classe_row' => 'rowDiluicao',
                            'parent' => 'array',
                            'name_child_class' => 'diluicao_adicionada',
                            'campos' => [
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
                'descricao' => 'O pagamento foi cancelado ou o contrato foi encerrado.',
            ],
        };
    }

    static public function statusPadraoSalvamento(): int
    {
        return self::AGUARDANDO_PAGAMENTO_EM_ANALISE->value;
    }

    static public function statusAceitaAlteracaoSimples(): array
    {
        return [
            self::AGUARDANDO_PAGAMENTO->value,
            // self::INADIMPLENTE->value,
            // self::INADIMPLENTE_EM_ANALISE->value,
        ];
    }

    static public function statusComMovimentacaoConta(): array
    {
        $movimentacaoCredito = [
            'movimentacao_tipo_id' => MovimentacaoContaTipoEnum::CREDITO->value,
            'movimentacao_tipo_id_rollback' => MovimentacaoContaTipoEnum::DEBITO->value,
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
        ];
    }

    // /**
    //  * Configuração dos tipos de status de Lançamentos. Usado no front para exibir as opções conforme cada tipo de Status do Lançamento.
    //  *
    //  * Chave do array: nome da ação.
    //  * Valor do array: array com as seguintes chaves:
    //  *   - id: id do status de lançamento.
    //  *   - cor: classe de cor Bootstrap para o botão.
    //  *   - opcao_nos_status: array com os ids dos status nos quais a ação está disponível.
    //  *
    //  * @return array
    //  */
    // static public function configAcoesLancamentoStatusTipoFinanLancServ()
    // {
    //     return [
    //         'AGUARDANDO_PAGAMENTO_EM_ANALISE' => [
    //             'id' => self::AGUARDANDO_PAGAMENTO_EM_ANALISE,
    //             'cor' => 'text-bg-warning',
    //             'opcao_nos_status' => [
    //                 self::AGUARDANDO_PAGAMENTO,
    //                 self::LIQUIDADO_EM_ANALISE,
    //                 self::LIQUIDADO,
    //                 self::CANCELADO_EM_ANALISE,
    //                 self::CANCELADO,
    //             ]
    //         ],
    //         'AGUARDANDO_PAGAMENTO' => [
    //             'id' => self::AGUARDANDO_PAGAMENTO,
    //             'cor' => null,
    //             'opcao_nos_status' => [
    //                 self::AGUARDANDO_PAGAMENTO_EM_ANALISE,
    //                 self::LIQUIDADO_EM_ANALISE,
    //                 self::LIQUIDADO,
    //                 self::CANCELADO_EM_ANALISE,
    //                 self::CANCELADO,
    //             ]
    //         ],
    //         'LIQUIDADO_EM_ANALISE' => [
    //             'id' => self::LIQUIDADO_EM_ANALISE,
    //             'cor' => 'text-success bg-warning',
    //             'opcao_nos_status' => [
    //                 self::AGUARDANDO_PAGAMENTO_EM_ANALISE,
    //                 self::AGUARDANDO_PAGAMENTO,
    //                 self::LIQUIDADO,
    //                 self::INADIMPLENTE_EM_ANALISE,
    //                 self::INADIMPLENTE,
    //             ]
    //         ],
    //         'LIQUIDADO' => [
    //             'id' => self::LIQUIDADO,
    //             'cor' => 'text-success',
    //             'opcao_nos_status' => [
    //                 self::AGUARDANDO_PAGAMENTO_EM_ANALISE,
    //                 self::AGUARDANDO_PAGAMENTO,
    //                 self::LIQUIDADO_EM_ANALISE,
    //                 self::INADIMPLENTE_EM_ANALISE,
    //                 self::INADIMPLENTE,
    //             ]
    //         ],
    //         'LIQUIDADO_PARCIALMENTE_EM_ANALISE' => [
    //             'id' => self::LIQUIDADO_PARCIALMENTE_EM_ANALISE,
    //             'opcao_nos_status' => []
    //         ],
    //         'LIQUIDADO_PARCIALMENTE' => [
    //             'id' => self::LIQUIDADO_PARCIALMENTE,
    //             'opcao_nos_status' => []
    //         ],
    //         'REAGENDADO_EM_ANALISE' => [
    //             'id' => self::REAGENDADO_EM_ANALISE,
    //             'opcao_nos_status' => []
    //         ],
    //         'REAGENDADO' => [
    //             'id' => self::REAGENDADO,
    //             'opcao_nos_status' => []
    //         ],
    //         'CANCELADO_EM_ANALISE' => [
    //             'id' => self::CANCELADO_EM_ANALISE,
    //             'opcao_nos_status' => []
    //         ],
    //         'CANCELADO' => [
    //             'id' => self::CANCELADO,
    //             'opcao_nos_status' => []
    //         ],
    //     ];
    // }
}
