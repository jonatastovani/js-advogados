<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum LancamentosCategoriaEnum: int
{
    use EnumTrait;

    case ENTRADA = 1;
    case PRIMEIRA_PARCELA = 2;
    case PARCELA = 3;

    public function detalhes(): array
    {
        return match ($this) {
            self::ENTRADA => [
                'id' => self::ENTRADA->value,
                'nome' => 'entrada',
                'label' => 'Entrada',
                'configuracao' => [
                    'personalizar_lancamento' => [
                        'campos_permitidos' => [
                            'forma_pagamento_id',
                            'observacao',
                        ],
                    ],
                ],
                'descricao' => 'Lançamento do tipo "Entrada" tem a data de vencimento e valor definidos nos dados do pagamento.',
            ],
            self::PRIMEIRA_PARCELA => [
                'id' => self::PRIMEIRA_PARCELA->value,
                'nome' => 'primeira_parcela',
                'label' => 'Primeira parcela',
                'configuracao' => [
                    'personalizar_lancamento' => [
                        'campos_permitidos' => [
                            'valor_esperado',
                            'forma_pagamento_id',
                            'observacao',
                        ],
                    ],
                ],
                'descricao' => 'Lançamento do tipo "Primeira parcela" tem a data de vencimento definida nos dados do pagamento.',
            ],
            self::PARCELA => [
                'id' => self::PARCELA->value,
                'nome' => 'parcela',
                'label' => 'Parcela',
                'configuracao' => [
                    'personalizar_lancamento' => [
                        'campos_permitidos' => ['*'],
                    ],
                ],
                'descricao' => 'Lançamento do tipo "Parcela" permite todas personalizações.',
            ],
        };
    }
}
