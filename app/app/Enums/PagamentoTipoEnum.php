<?php

namespace App\Enums;

use App\Helpers\PagamentoTipoEntradaComParcelamentoHelper;
use App\Helpers\PagamentoTipoPagamentoUnicoHelper;
use App\Helpers\PagamentoTipoParceladoHelper;
use App\Helpers\PagamentoTipoRecorrenteHelper;
use App\Traits\EnumTrait;

enum PagamentoTipoEnum: int
{
    use EnumTrait;

    case PAGAMENTO_UNICO = 1;
    case PARCELADO = 2;
    case ENTRADA_COM_PARCELAMENTO = 3;
    case RECORRENTE = 4;
    case CONDICIONADO = 5;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::PAGAMENTO_UNICO => [
                'id' => self::PAGAMENTO_UNICO->value,
                'nome' => 'Pagamento Único',
                'descricao' => 'Método para registrar uma pagamento único.',
                'campos_obrigatorios' => [
                    [
                        'nome' => 'entrada_data',
                        'nome_exibir' => 'Vencimento',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'valor_total',
                        'nome_exibir' => 'Valor total',
                        'form_request_rule' => 'required|numeric|min:1',
                    ],
                ],
                'helper' => [
                    'class' => PagamentoTipoPagamentoUnicoHelper::class,
                    'endpoint_api' => 'api/helper/pagamento-tipo/pagamento-unico',
                ],
            ],
            self::PARCELADO => [
                'id' => self::PARCELADO->value,
                'nome' => 'Parcelado',

                'descricao' => "Método que divide o valor total pela quantidade de parcelas informadas.",
                'campos_obrigatorios' => [
                    [
                        'nome' => 'parcela_data_inicio',
                        'nome_exibir' => 'Vencimento primeira',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'parcela_quantidade',
                        'nome_exibir' => 'Quantidade de parcelas',
                        'form_request_rule' => 'required|integer|min:1',
                    ],
                    [
                        'nome' => 'parcela_vencimento_dia',
                        'nome_exibir' => 'Dia de vencimento',
                        'form_request_rule' => 'required|integer|min:1|max:31',
                    ],
                    [
                        'nome' => 'valor_total',
                        'nome_exibir' => 'Valor total',
                        'form_request_rule' => 'required|numeric|min:1',
                    ],
                ],
                'helper' => [
                    'class' => PagamentoTipoParceladoHelper::class,
                    'endpoint_api' => 'api/helper/pagamento-tipo/parcelado',
                ],
            ],
            self::ENTRADA_COM_PARCELAMENTO => [
                'id' => self::ENTRADA_COM_PARCELAMENTO->value,
                'nome' => 'Entrada + Parcelamento',
                'descricao' => 'Método para registrar uma entrada com parcelamento. O valor da entrada será subtraído do valor total antes de gerar as parcelas.',
                'campos_obrigatorios' => [
                    [
                        'nome' => 'entrada_valor',
                        'nome_exibir' => 'Vencimento entrada',
                        'form_request_rule' => 'required|numeric|min:1',
                    ],
                    [
                        'nome' => 'entrada_data',
                        'nome_exibir' => 'Data da entrada',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'parcela_data_inicio',
                        'nome_exibir' => 'Vencimento primeira',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'parcela_quantidade',
                        'nome_exibir' => 'Quantidade de parcelas',
                        'form_request_rule' => 'required|integer|min:1',
                    ],
                    [
                        'nome' => 'parcela_vencimento_dia',
                        'nome_exibir' => 'Dia de vencimento',
                        'form_request_rule' => 'required|integer|min:1|max:31',
                    ],
                    [
                        'nome' => 'valor_total',
                        'nome_exibir' => 'Valor total',
                        'form_request_rule' => 'required|numeric|min:1',
                    ],
                ],
                'helper' => [
                    'class' => PagamentoTipoEntradaComParcelamentoHelper::class,
                    'endpoint_api' => 'api/helper/pagamento-tipo/entrada-com-parcelamento',
                ],
            ],
            self::RECORRENTE => [
                'id' => self::RECORRENTE->value,
                'nome' => 'Recorrente',
                'descricao' => "Método que gera as parcelas de maneira recorrente, podendo definir a data de início, data final (opcional) e a maneira de recorrência das parcelas.",
                'campos_obrigatorios' => [
                    [
                        'nome' => 'cron_data_inicio',
                        'nome_exibir' => 'Data Início',
                        'form_request_rule' => 'required|date',
                    ],
                    [
                        'nome' => 'cron_expressao',
                        'nome_exibir' => 'Expressão recorrência',
                        'form_request_rule' => 'nullable|string',
                        'attr' => ['visible' => false],
                    ],
                    [
                        'nome' => 'parcela_valor',
                        'nome_exibir' => 'Valor da parcela',
                        'form_request_rule' => 'required|numeric|min:1',
                    ],
                ],
                'campos_opcionais' => [
                    [
                        'nome' => 'cron_data_fim',
                        'nome_exibir' => 'Data Fim',
                        'form_request_rule' => 'nullable|date',
                    ],
                ],
                'helper' => [
                    'class' => PagamentoTipoRecorrenteHelper::class,
                    'endpoint_api' => 'api/helper/pagamento-tipo/recorrente',
                ],
            ],
            self::CONDICIONADO => [
                'id' => self::CONDICIONADO->value,
                'nome' => 'Condicionado',
                'descricao' => "Método que não espera um valor total, mas exige uma descrição da condição para o pagamento.",
                'campos_obrigatorios' => [
                    [
                        'nome' => 'descricao_condicionado',
                        'nome_exibir' => 'Descrição do pagamento condicionado',
                        'form_request_rule' => 'required|string',
                    ],
                ],
            ],
        };
    }

    static public function pagamentoTipoQuePermiteLiquidadoMigracao(): array
    {
        return [
            self::PAGAMENTO_UNICO->value,
            self::PARCELADO->value,
            self::ENTRADA_COM_PARCELAMENTO->value,
            self::RECORRENTE->value,
        ];
    }

    static public function pagamentoTipoComLancamentosPersonalizaveis(): array
    {
        return [
            self::PARCELADO->value,
            self::ENTRADA_COM_PARCELAMENTO->value,
            self::RECORRENTE->value,
        ];
    }

    static public function pagamentoTipoComLancamentosCategoriaEntrada(): array
    {
        return [
            PagamentoTipoEnum::PAGAMENTO_UNICO->value,
            PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value,
        ];
    }

    static public function pagamentoTipoComLancamentosCategoriaPrimeiraParcela(): array
    {
        return [
            PagamentoTipoEnum::PARCELADO->value,
            PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value,
        ];
    }

    static public function pagamentoTipoComConferenciaDeValorTotal(): array
    {
        return [
            PagamentoTipoEnum::PAGAMENTO_UNICO->value,
            PagamentoTipoEnum::PARCELADO->value,
            PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value,
        ];
    }

    static public function pagamentoTipoComConferenciaDeNumeroDeParcelas(): array
    {
        return [
            PagamentoTipoEnum::PARCELADO->value,
            PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value,
        ];
    }

    static public function pagamentoTipoComLancamentosDependentesValorTotal(): array
    {
        return [
            self::PARCELADO->value,
            self::ENTRADA_COM_PARCELAMENTO->value,
        ];
    }

    static public function pagamentoTipoCamposLancamentosPersonalizados(): array
    {
        return [
            [
                'nome' => 'forma_pagamento_id',
                'nome_exibir' => 'Forma de pagamento',
                'form_request_rule' => 'nullable|uuid',
                'validacao_front' => [
                    'tipo' => 'uuid',
                    'acao_se_invalido' => 'remover', // ou 'alertar'
                    'mensagem' => 'Forma de pagamento inválida. O campo será ignorado.',
                ],
            ],
            [
                'nome' => 'valor_esperado',
                'nome_exibir' => 'Valor esperado',
                'form_request_rule' => 'required|numeric|min:1',
                'validacao_front' => [
                    'tipo' => 'numeric',
                    'acao_se_invalido' => 'alertar',
                    'mensagem' => 'Informe um valor esperado válido (mínimo R$ 1,00).',
                ],
            ],
            [
                'nome' => 'data_vencimento',
                'nome_exibir' => 'Data de vencimento',
                'form_request_rule' => 'required|date',
                'validacao_front' => [
                    'tipo' => 'date',
                    'acao_se_invalido' => 'alertar',
                    'mensagem' => 'Informe uma data de vencimento válida.',
                ],
            ],
            [
                'nome' => 'descricao_automatica',
                'nome_exibir' => 'Descrição automática',
                'form_request_rule' => 'required|string|max:255',
                'validacao_front' => [
                    'tipo' => 'string',
                    'acao_se_invalido' => 'ignorar',
                    'mensagem' => '',
                ],
            ],
            [
                'nome' => 'observacao',
                'nome_exibir' => 'Observação',
                'form_request_rule' => 'nullable|string|max:500',
                'validacao_front' => [
                    'tipo' => 'string',
                    'acao_se_invalido' => 'ignorar',
                    'mensagem' => '',
                ],
            ],
            [
                'nome' => 'lancamento_categoria_id',
                'nome_exibir' => 'Categoria',
                'form_request_rule' => 'required|integer',
                'validacao_front' => [
                    'tipo' => 'integer',
                    'acao_se_invalido' => 'alertar',
                    'mensagem' => 'A informação de categoria é obrigatória. Se o problema persistir, contate o desenvolvedor.',
                ],
            ],
        ];
    }
}
