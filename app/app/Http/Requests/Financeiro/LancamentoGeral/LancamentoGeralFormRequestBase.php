<?php

namespace App\Http\Requests\Financeiro\LancamentoGeral;

use App\Common\RestResponse;
use App\Enums\PagamentoTipoEnum;
use App\Helpers\LogHelper;
use App\Http\Requests\BaseFormRequest;
use App\Models\Tenant\PagamentoTipoTenant;
use Illuminate\Support\Fluent;

class LancamentoGeralFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            // 'status_id' => 'required|integer',
            'conta_id' => 'nullable|uuid',
            'observacao' => 'nullable|string',
        ];

        return $rules;
    }

}
