<?php

namespace App\Http\Requests\Financeiro\LancamentoGeral;

use App\Http\Requests\BaseFormRequest;

class LancamentoGeralFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            'movimentacao_tipo_id' => 'required|integer',
            'descricao' => 'required|string',
            'valor_esperado' => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date',
            'categoria_id' => 'nullable|uuid',
            'conta_id' => 'nullable|uuid',
            'agendamento_id' => 'nullable|uuid',
            // 'movimentacao_status_tipo_id' => 'nullable|integer',
            'observacao' => 'nullable|string',
        ];

        return $rules;
    }
}
