<?php

namespace App\Helpers;

use App\Models\Financeiro\Conta;
use Illuminate\Support\Fluent;

class PagamentoTipoPagamentoUnicoHelper
{
    static public function renderizar(Fluent $dados, array $options = [])
    {

        $conta = Conta::find($dados->conta_id);

        return [
            'lancamentos' => [
                [
                    'descricao_automatica' => 'Pagamento Único',
                    'observacao' => null,
                    'data_vencimento' => $dados->entrada_data,
                    'valor_esperado' => $dados->valor_total,
                    'status' => ['nome' => 'Simulado'],
                    'conta_id' => $conta->id,
                    'conta' => $conta,
                ]
            ]
        ];
    }
}
