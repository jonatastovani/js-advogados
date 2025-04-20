<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Enums\PagamentoTipoEnum;
use App\Models\Referencias\PagamentoTipo;

class PagamentoTipoFormRequestRenderRecorrente extends PagamentoTipoFormRequestRenderBase
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->buildRulesFromTipoPagamento(PagamentoTipoEnum::RECORRENTE->value);
    }

    // /**
    //  * Get the validation rules that apply to the request.
    //  *
    //  * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    //  */
    // public function rules(): array
    // {
    //     $pagamentoTipo = PagamentoTipo::find(PagamentoTipoEnum::RECORRENTE->value);
    //     $rules = parent::rules();
    //     foreach ($pagamentoTipo->campos_obrigatorios as $value) {
    //         $rules[$value['nome']] = $value['form_request_rule'];
    //     }
    //     return $rules;
    // }
}
