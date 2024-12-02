<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use Illuminate\Support\Arr;

class PostConsultaFiltroFormRequestMovimentacaoConta extends PostConsultaFiltroFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        // Previne o recebimento das regras de intervalo de datas
        $rules = Arr::except(parent::rules(), [
            'mes_ano'
        ]);

        $rules = array_merge($rules, [
            'datas_intervalo' => 'required|array',
            'datas_intervalo.campo_data' => 'required|string',
            'datas_intervalo.data_inicio' => 'required|date',
            'datas_intervalo.data_fim' => 'required|date',
            'conta_id' => 'nullable|uuid',
            'movimentacao_tipo_id' => 'nullable|integer',
            'movimentacao_status_tipo_id' => 'nullable|integer',
        ]);
        return $rules;
    }
}
