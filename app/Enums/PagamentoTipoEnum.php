<?php

namespace App\Enums;

use App\Helpers\PagamentoTipoCondicionadoHelper;
use App\Helpers\PagamentoTipoEntradaComParcelamentoHelper;
use App\Helpers\PagamentoTipoPagamentoUnicoHelper;
use App\Helpers\PagamentoTipoParceladoHelper;
use App\Helpers\PagamentoTipoRecorrenteHelper;

enum PagamentoTipoEnum: int
{
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
                'id' => self::PAGAMENTO_UNICO,
                'nome' => 'Pagamento Único',
                'descricao' => 'Método para registrar uma pagamento único.',
                'configuracao' => [
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'entrada_data',
                            'nome_exibir' => 'Vencimento',
                            'formRequestRule' => 'required|date',
                        ],
                        [
                            'nome' => 'valor_total',
                            'nome_exibir' => 'Valor total',
                            'formRequestRule' => 'required|numeric',
                        ],
                    ],
                    'helper' => [
                        'class' => PagamentoTipoPagamentoUnicoHelper::class,
                        'endpoint_api' => 'api/helper/pagamento-tipo/pagamento-unico',
                    ]
                ],
            ],
            self::PARCELADO => [
                'id' => self::PARCELADO,
                'nome' => 'Parcelado',

                'descricao' => "Método que divide o valor total pela quantidade de parcelas informadas.",
                'configuracao' => [
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'parcela_data_inicio',
                            'nome_exibir' => 'Vencimento primeira',
                            'formRequestRule' => 'required|date',
                        ],
                        [
                            'nome' => 'parcela_quantidade',
                            'nome_exibir' => 'Quantidade de parcelas',
                            'formRequestRule' => 'required|integer|min:1',
                        ],
                        [
                            'nome' => 'parcela_vencimento_dia',
                            'nome_exibir' => 'Dia de vencimento',
                            'formRequestRule' => 'required|integer|min:1|max:31',
                        ],
                        [
                            'nome' => 'valor_total',
                            'nome_exibir' => 'Valor total',
                            'formRequestRule' => 'required|numeric',
                        ],
                    ],
                    'helper' => [
                        'class' => PagamentoTipoParceladoHelper::class,
                        'endpoint_api' => 'api/helper/pagamento-tipo/parcelado',
                    ]
                ],
            ],
            self::ENTRADA_COM_PARCELAMENTO => [
                'id' => self::ENTRADA_COM_PARCELAMENTO,
                'nome' => 'Entrada + Parcelamento',
                'descricao' => 'Método para registrar uma entrada com parcelamento. O valor da entrada será subtraído do valor total antes de gerar as parcelas.',
                'configuracao' => [
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'entrada_valor',
                            'nome_exibir' => 'Vencimento entrada',
                            'formRequestRule' => 'required|numeric',
                        ],
                        [
                            'nome' => 'entrada_data',
                            'nome_exibir' => 'Data da entrada',
                            'formRequestRule' => 'required|date',
                        ],
                        [
                            'nome' => 'parcela_data_inicio',
                            'nome_exibir' => 'Vencimento primeira',
                            'formRequestRule' => 'required|date',
                        ],
                        [
                            'nome' => 'parcela_quantidade',
                            'nome_exibir' => 'Quantidade de parcelas',
                            'formRequestRule' => 'required|integer|min:1',
                        ],
                        [
                            'nome' => 'parcela_vencimento_dia',
                            'nome_exibir' => 'Dia de vencimento',
                            'formRequestRule' => 'required|integer|min:1|max:31',
                        ],
                        [
                            'nome' => 'valor_total',
                            'nome_exibir' => 'Valor total',
                            'formRequestRule' => 'required|numeric',
                        ],
                    ],
                    'helper' => [
                        'class' => PagamentoTipoEntradaComParcelamentoHelper::class,
                        'endpoint_api' => 'api/helper/pagamento-tipo/entrada-com-parcelamento',
                    ]
                ],
            ],
            self::RECORRENTE => [
                'id' => self::RECORRENTE,
                'nome' => 'Recorrente',
                'descricao' => "Método que gera as parcelas de maneira recorrente, podendo definir a data de início e o dia de vencimento das parcelas. Este método não pode ser combinado com nenhum outro.",
                'configuracao' => [
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'parcela_data_inicio',
                            'nome_exibir' => 'Vencimento primeira',
                            'formRequestRule' => 'required|date',
                        ],
                        [
                            'nome' => 'parcela_vencimento_dia',
                            'nome_exibir' => 'Dia de vencimento',
                            'formRequestRule' => 'required|integer|min:1|max:31',
                        ],
                        [
                            'nome' => 'parcela_valor',
                            'nome_exibir' => 'Valor da parcela',
                            'formRequestRule' => 'required|numeric',
                        ],
                    ],
                    'helper' => [
                        'class' => PagamentoTipoRecorrenteHelper::class,
                        'endpoint_api' => 'api/helper/pagamento-tipo/recorrente',
                    ]
                ],
            ],
            self::CONDICIONADO => [
                'id' => self::CONDICIONADO,
                'nome' => 'Condicionado',
                'descricao' => "Método que não espera um valor total, mas exige uma descrição da condição para o pagamento. Este método não pode ser combinado com nenhum outro.",
                'configuracao' => [
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'descricao_condicionado',
                            'nome_exibir' => 'Descrição do pagamento condicionado',
                            'formRequestRule' => 'required|string',
                        ],
                    ],
                    'helper' => [
                        'class' => PagamentoTipoCondicionadoHelper::class,
                        'endpoint_api' => 'api/helper/pagamento-tipo/condicionado',
                    ]
                ],
            ],
        };
    }
}
