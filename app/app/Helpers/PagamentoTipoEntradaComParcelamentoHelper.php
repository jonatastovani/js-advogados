<?php

namespace App\Helpers;

use App\Enums\LancamentosCategoriaEnum;
use App\Models\Tenant\FormaPagamentoTenant;
use App\Traits\ParcelamentoTipoHelperTrait;
use Illuminate\Support\Fluent;

class PagamentoTipoEntradaComParcelamentoHelper
{
    use ParcelamentoTipoHelperTrait;

    static public function renderizar(Fluent $dados, array $options = [])
    {
        $lancamentos = [];
        $valorTotal = $dados->valor_total;
        $valorEntrada = $dados->entrada_valor;
        $dataEntrada = $dados->entrada_data;
        $quantidadeParcelas = $dados->parcela_quantidade;
        $dataInicio = $dados->parcela_data_inicio;
        $diaVencimento = $dados->parcela_vencimento_dia;

        // $formaPagamento = FormaPagamentoTenant::find($dados->forma_pagamento_id);

        // Calcula o valor das parcelas após descontar a entrada
        $valorRestante = bcsub($valorTotal, $valorEntrada, 2);
        $valorParcela = bcdiv($valorRestante, $quantidadeParcelas, 2);
        $valorTotalParcelas = bcmul($valorParcela, $quantidadeParcelas, 2);

        // Ajustar a diferença centesimal na primeira parcela
        $diferenca = bcsub($valorRestante, $valorTotalParcelas, 2);

        $dataEntrada = new \DateTime($dataEntrada);

        // Adiciona a entrada como primeira "parcela"
        $lancamentos[] = [
            'descricao_automatica' => 'Entrada',
            'lancamento_categoria_id' => LancamentosCategoriaEnum::ENTRADA->value,
            'observacao' => null,
            'data_vencimento' => $dataEntrada->format('Y-m-d'),
            'valor_esperado' => round((float) $valorEntrada, 2),
            'status' => ['nome' => 'Simulado'],
            // 'forma_pagamento_id' => $formaPagamento->id,
            // 'forma_pagamento' => $formaPagamento,
        ];

        $dataVencimento = new \DateTime($dataInicio);

        // Gerar as parcelas restantes
        for ($i = 1; $i <= $quantidadeParcelas; $i++) {
            $valorParcelaAjustada = ($i === 1)
                ? bcadd($valorParcela, $diferenca, 2)
                : $valorParcela;

            $lancamentos[] = [
                'descricao_automatica' => "Parcela {$i} de {$quantidadeParcelas}",
                'lancamento_categoria_id' => $i == 1 ? LancamentosCategoriaEnum::PRIMEIRA_PARCELA->value : LancamentosCategoriaEnum::PARCELA->value,
                'observacao' => null,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'valor_esperado' => round((float) $valorParcelaAjustada, 2),
                'status' => ['nome' => 'Simulado'],
                // 'forma_pagamento_id' => $formaPagamento->id,
                // 'forma_pagamento' => $formaPagamento,
            ];

            $dataVencimento = self::ajustarDataVencimentoSeguinte($dataVencimento, $diaVencimento);
        }

        return ['lancamentos' => $lancamentos];
    }
}
