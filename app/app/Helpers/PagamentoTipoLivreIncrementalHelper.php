<?php

namespace App\Helpers;

use App\Enums\LancamentosCategoriaEnum;
use App\Models\Tenant\FormaPagamentoTenant;
use App\Traits\ParcelamentoTipoHelperTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;

class PagamentoTipoLivreIncrementalHelper
{
    use ParcelamentoTipoHelperTrait;

    static public function renderizar(Fluent $dados, array $options = [])
    {
        $lancamentos = [];
        $quantidadeParcelas = $dados->parcela_quantidade;
        $mesAnoInicio = new \DateTime($dados->parcela_mes_ano_inicio);
        $diaVencimento = $dados->parcela_vencimento_dia;
        $valorParcela = $dados->parcela_valor;

        $formapagamento = FormaPagamentoTenant::find($dados->forma_pagamento_id);
        $dataVencimento = Carbon::instance(self::ajustarDataVencimentoPrimeiraParcela($mesAnoInicio->format('Y-m'), $diaVencimento));

        // Gerar as parcelas
        for ($i = 1; $i <= $quantidadeParcelas; $i++) {

            $lancamentos[] = [
                'descricao_automatica' => "Personalizada",
                'lancamento_categoria_id' => LancamentosCategoriaEnum::PARCELA->value,
                'observacao' => null,
                'data_vencimento' => $dataVencimento->format('Y-m-d'),
                'valor_esperado' => round((float) $valorParcela, 2),
                'status' => ['nome' => 'Simulado'],
                'forma_pagamento_id' => $formapagamento->id,
                'forma_pagamento' => $formapagamento,
            ];

            // Ajusta a data para o próximo mês
            $dataVencimento = self::ajustarDataVencimentoSeguinte($dataVencimento, $diaVencimento);
        }

        return ['lancamentos' => $lancamentos];
    }
}
