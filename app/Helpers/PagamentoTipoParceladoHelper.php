<?php

namespace App\Helpers;

use App\Models\Financeiro\Conta;
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

        $conta = Conta::find($dados->conta_id);

        $valorParcela = floor($valorTotal / $quantidadeParcelas * 100) / 100; // Arredondar para baixo com duas casas decimais
        $valorTotalParcelas = $valorParcela * $quantidadeParcelas;

        // Ajustar a diferença centesimal na primeira parcela
        $diferenca = round(($valorTotal - $valorTotalParcelas), 2);
        $dataVencimento = new \DateTime($dataInicio);

        // Gerar as parcelas restantes
        for ($i = 1; $i <= $quantidadeParcelas; $i++) {
            $valorParcelaAjustada = ($i === 1) ? $valorParcela + $diferenca : $valorParcela;

            $lancamentos[] = [
                'descricao_automatica' => "Parcela {$i} de {$quantidadeParcelas}",
                'observacao' => null,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'valor_esperado' => round($valorParcelaAjustada, 2),
                'status' => ['nome' => 'Simulado'],
                'conta_id' => $conta->id,
                'conta' => $conta,
            ];

            // Ajusta a data para o próximo mês
            $dataVencimento = self::ajustarDataVencimentoSeguinte($dataVencimento, $diaVencimento);
        }

        return ['lancamentos' => $lancamentos];
    }
}
