<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Enums\PagamentoTipoEnum;
use App\Models\Referencias\PagamentoTipo;

class PagamentoTipoFormRequestRenderPagamentoUnico extends PagamentoTipoFormRequestRenderBase
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pagamentoTipo = PagamentoTipo::find(PagamentoTipoEnum::PAGAMENTO_UNICO->value);
        $rules = parent::rules();
        foreach ($pagamentoTipo->configuracao['campos_obrigatorios'] as $value) {
            $rules[$value['nome']] = $value['form_request_rule'];
        }
        return $rules;
    }
}
