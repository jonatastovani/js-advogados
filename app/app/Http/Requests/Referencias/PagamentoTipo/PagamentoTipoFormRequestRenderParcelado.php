<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Enums\PagamentoTipoEnum;
use App\Models\Referencias\PagamentoTipo;

class PagamentoTipoFormRequestRenderParcelado extends PagamentoTipoFormRequestRenderBase
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
        $pagamentoTipo = PagamentoTipo::find(PagamentoTipoEnum::PARCELADO->value);
        $rules = parent::rules();
        foreach ($pagamentoTipo->campos_obrigatorios as $value) {
            if ($value['nome'] == 'valor_total') {
                $value['form_request_rule'] = str_replace('min:0.01', "min:" . (request('parcela_quantidade') * 0.01), $value['form_request_rule']);
            }
            $rules[$value['nome']] = $value['form_request_rule'];
        }
        return $rules;
    }
}
