<?php

namespace App\Http\Requests\Financeiro\MovimentacaoContaParticipante;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use Illuminate\Support\Arr;

class PostConsultaFiltroFormRequestBalancoRepasse extends PostConsultaFiltroFormRequestBase
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
            'mes_ano',
            'datas_intervalo.campo_data',
        ]);

        $rules = array_merge($rules, [
            'datas_intervalo' => 'nullable|array',
            'datas_intervalo.data_inicio' => 'required|date',
            'datas_intervalo.data_fim' => 'required|date',
            'perfil_id' => 'required|uuid',
            'conta_id' => 'nullable|uuid',
            'movimentacao_tipo_id' => 'nullable|integer',
            'movimentacao_status_tipo_id' => 'nullable|integer',
        ]);
        return $rules;
    }
}
