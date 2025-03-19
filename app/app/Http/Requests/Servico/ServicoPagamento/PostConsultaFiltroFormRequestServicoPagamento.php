<?php

namespace App\Http\Requests\Servico\ServicoPagamento;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use Illuminate\Support\Arr;

class PostConsultaFiltroFormRequestServicoPagamento extends PostConsultaFiltroFormRequestBase
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
            'pagamento_tipo_tenant_id' => 'nullable|uuid',
            'pagamento_status_tipo_id' => 'nullable|integer',
            'area_juridica_id' => 'nullable|uuid',
        ]);
        return $rules;
    }
}
