<?php

namespace App\Helpers;

use App\Models\Financeiro\Conta;
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

        $conta = Conta::find($dados->conta_id);

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
            'observacao' => null,
            'data_vencimento' => $dataEntrada->format('Y-m-d'),
            'valor_esperado' => $valorEntrada,
            'status' => ['nome' => 'Simulado'],
            'conta_id' => $conta->id,
            'conta' => $conta,
        ];

        $dataVencimento = new \DateTime($dataInicio);

        // Gerar as parcelas restantes
        for ($i = 1; $i <= $quantidadeParcelas; $i++) {
            $valorParcelaAjustada = ($i === 1) 
                ? bcadd($valorParcela, $diferenca, 2) 
                : $valorParcela;

            $lancamentos[] = [
                'descricao_automatica' => "Parcela {$i} de {$quantidadeParcelas}",
                'observacao' => null,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'valor_esperado' => $valorParcelaAjustada,
                'status' => ['nome' => 'Simulado'],
                'conta_id' => $conta->id,
                'conta' => $conta,
            ];

            $dataVencimento = self::ajustarDataVencimentoSeguinte($dataVencimento, $diaVencimento);
        }

        return ['lancamentos' => $lancamentos];
    }
}
