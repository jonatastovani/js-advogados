<?php

namespace App\Http\Requests\Financeiro\LancamentoRessarcimento;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use Illuminate\Support\Arr;

class PostConsultaFiltroFormRequestLancamentoRessarcimento extends PostConsultaFiltroFormRequestBase
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
            'datas_intervalo',
            'datas_intervalo.campo_data',
            'datas_intervalo.data_inicio',
            'datas_intervalo.data_fim'
        ]);

        $rules = array_merge($rules, [
            'mes_ano' => 'required|date:Y-m',
            'conta_id' => 'nullable|uuid',
            'movimentacao_tipo_id' => 'nullable|integer',
            'lancamento_status_tipo_id' => 'nullable|integer',
            'categoria_id' => 'nullable|uuid',
        ]);
        return $rules;
    }
}
