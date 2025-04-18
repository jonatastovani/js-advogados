<?php

namespace App\Helpers;

use App\Models\Tenant\FormaPagamentoTenant;
use App\Traits\ParcelamentoTipoHelperTrait;
use Illuminate\Support\Fluent;

class PagamentoTipoParceladoHelper
{
    use ParcelamentoTipoHelperTrait;

    static public function renderizar(Fluent $dados, array $options = [])
    {
        $lancamentos = [];
        $valorTotal = $dados->valor_total;
        $quantidadeParcelas = $dados->parcela_quantidade;
        $dataInicio = $dados->parcela_data_inicio;
        $diaVencimento = $dados->parcela_vencimento_dia;

        // $formapagamento = FormaPagamentoTenant::find($dados->forma_pagamento_id);

        $valorParcela = bcdiv($valorTotal, $quantidadeParcelas, 2); // Divisão precisa com 2 casas decimais
        $valorTotalParcelas = bcmul($valorParcela, $quantidadeParcelas, 2);

        // Ajustar a diferença centesimal na primeira parcela
        $diferenca = bcsub($valorTotal, $valorTotalParcelas, 2);
        $dataVencimento = new \DateTime($dataInicio);

        // Gerar as parcelas
        for ($i = 1; $i <= $quantidadeParcelas; $i++) {
            $valorParcelaAjustada = ($i === 1)
                ? bcadd($valorParcela, $diferenca, 2)
                : $valorParcela;

            $lancamentos[] = [
                'descricao_automatica' => "Parcela {$i} de {$quantidadeParcelas}",
                'categoria_lancamento' => 'parcela',
                'observacao' => null,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'valor_esperado' => round((float) $valorParcelaAjustada, 2),
                'status' => ['nome' => 'Simulado'],
                // 'forma_pagamento_id' => $formapagamento->id,
                // 'forma_pagamento' => $formapagamento,
            ];

            // Ajusta a data para o próximo mês
            $dataVencimento = self::ajustarDataVencimentoSeguinte($dataVencimento, $diaVencimento);
        }

        return ['lancamentos' => $lancamentos];
    }
}
