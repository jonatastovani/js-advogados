<?php

namespace App\Helpers;

use App\Models\Tenant\FormaPagamentoTenant;
use Illuminate\Support\Fluent;

class PagamentoTipoPagamentoUnicoHelper
{
    static public function renderizar(Fluent $dados, array $options = [])
    {

        // $formapagamento = FormaPagamentoTenant::find($dados->forma_pagamento_id);

        return [
            'lancamentos' => [
                [
                    'descricao_automatica' => 'Pagamento Único',
                    'categoria_lancamento' => 'entrada',
                    'observacao' => null,
                    'data_vencimento' => $dados->entrada_data,
                    'valor_esperado' => round((float) $dados->valor_total, 2),
                    'status' => ['nome' => 'Simulado'],
                    // 'forma_pagamento_id' => $formapagamento->id,
                    // 'forma_pagamento' => $formapagamento,
                ]
            ]
        ];
    }
}
