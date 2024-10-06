<?php

namespace Database\Seeders\Referencia;

use App\Helpers\UUIDsHelpers;
use App\Models\Referencias\PagamentoTipo;
use Illuminate\Database\Seeder;

class PagamentoTipoSeeder extends Seeder
{
    public function run(): void
    {
        $insert = [
            [
                'id' => 1,
                'nome' => 'Entrada',
                'descricao' => 'Método para registrar uma entrada ou parcela única, podendo definir data de entrada/pagamento único.',
                'configuracao' => [
                    'metodos_aliados' => [2, 3],
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'entrada_valor',
                            'nome_exibir' => 'Valor da entrada',
                        ],
                        [
                            'nome' => 'entrada_data',
                            'nome_exibir' => 'Data da entrada',
                        ],
                        [
                            'nome' => 'valor_total',
                            'nome_exibir' => 'Valor total',
                        ],
                    ],
                    'validacao' => [
                        'php' => ['App\Services\Validacao\PagamentoTipoEntradaValidacao', 'executa'],
                        'js' => 'validacao/entrada-tipo',
                    ]
                ],
            ],
            [
                'id' => 2,
                'nome' => 'Parcelado',
                'descricao' => "Método que divide o valor total pela quantidade de parcelas informadas. Quando combinado com o método 'Entrada', o valor da entrada é subtraído do valor total antes de gerar as parcelas.",
                'configuracao' => [
                    'metodos_aliados' => [1],
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'parcela_data_inicio',
                            'nome_exibir' => 'Data da primeira parcela',
                        ],
                        [
                            'nome' => 'parcela_quantidade',
                            'nome_exibir' => 'Quantidade de parcelas',
                        ],
                        [
                            'nome' => 'parcela_vencimento_dia',
                            'nome_exibir' => 'Dia de vencimento',
                        ],
                        [
                            'nome' => 'valor_total',
                            'nome_exibir' => 'Valor total',
                        ],
                    ],
                    'validacao' => [
                        'php' => ['App\Services\Validacao\PagamentoTipoParceladoValidacao', 'executa'],
                        'js' => 'validacao/parcelado-tipo',
                    ]
                ],
            ],
            [
                'id' => 3,
                'nome' => 'Recorrente',
                'descricao' => "Método que gera as parcelas de maneira recorrente, podendo definir a data de início e o dia de vencimento das parcelas. Este método não pode ser combinado com nenhum outro.",
                'configuracao' => [
                    'metodos_aliados' => [],
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'parcela_data_inicio',
                            'nome_exibir' => 'Data da primeira parcela',
                        ],
                        [
                            'nome' => 'parcela_vencimento_dia',
                            'nome_exibir' => 'Dia de vencimento',
                        ],
                        [
                            'nome' => 'parcela_valor',
                            'nome_exibir' => 'Valor da parcela',
                        ],
                    ],
                    'validacao' => [
                        'php' => ['App\Services\Validacao\PagamentoTipoRecorrenteValidacao', 'executa'],
                        'js' => 'validacao/recorrente-tipo',
                    ]
                ],
            ],
            [
                'id' => 4,
                'nome' => 'Condicionado',
                'descricao' => "Método que não espera um valor total, mas exige uma descrição da condição para o pagamento. Este método não pode ser combinado com nenhum outro.",
                'configuracao' => [
                    'metodos_aliados' => [],
                    'campos_obrigatorios' => [
                        [
                            'nome' => 'descricao_condicionado',
                            'nome_exibir' => 'Descrição do pagamento condicionado',
                        ],
                    ],
                    'validacao' => [
                        'php' => ['App\Services\Validacao\PagamentoTipoCondicionadoValidacao', 'executa'],
                        'js' => 'validacao/condicionado-tipo',
                    ]
                ],
            ]
        ];

        $adminTenantUserId = UUIDsHelpers::getAdminTenantUser();
        foreach ($insert as $data) {
            $data['created_user_id'] = $adminTenantUserId;
            PagamentoTipo::create($data);
        }
    }
}
