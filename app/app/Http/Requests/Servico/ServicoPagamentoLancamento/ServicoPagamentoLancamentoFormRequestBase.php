<?php

namespace App\Http\Requests\Servico\ServicoPagamentoLancamento;

use App\Common\RestResponse;
use App\Enums\PagamentoTipoEnum;
use App\Helpers\LogHelper;
use App\Http\Requests\BaseFormRequest;
use App\Models\Tenant\PagamentoTipoTenant;
use Illuminate\Support\Fluent;

class ServicoPagamentoLancamentoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            // 'status_id' => 'required|integer',
            'forma_pagamento_id' => 'nullable|uuid',
            'observacao' => 'nullable|string',
        ];

        return $rules;
    }

}
