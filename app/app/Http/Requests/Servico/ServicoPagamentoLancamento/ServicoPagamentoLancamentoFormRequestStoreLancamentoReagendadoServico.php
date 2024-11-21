<?php

namespace App\Http\Requests\Servico\ServicoPagamentoLancamento;

use App\Http\Requests\BaseFormRequest;

class ServicoPagamentoLancamentoFormRequestStoreLancamentoReagendadoServico extends BaseFormRequest
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
        return [
            'data_vencimento' => 'required|date',
        ];
    }
}
