<?php

namespace App\Http\Requests\Servico\ServicoPagamentoLancamento;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use Illuminate\Support\Arr;

class PostConsultaFiltroFormRequestServicoPagamentoLancamento extends PostConsultaFiltroFormRequestBase
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
            'forma_pagamento_id' => 'nullable|uuid',
            'lancamento_status_tipo_id' => 'nullable|integer',
            'area_juridica_id' => 'nullable|uuid',
            'status_extras' => 'nullable|array',
            'status_extras.*' => 'nullable|integer',
        ]);
        return $rules;
    }
}
