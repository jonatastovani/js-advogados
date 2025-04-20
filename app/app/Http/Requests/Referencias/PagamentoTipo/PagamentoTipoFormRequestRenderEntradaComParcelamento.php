<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Enums\PagamentoTipoEnum;
use App\Models\Referencias\PagamentoTipo;
use Illuminate\Support\Fluent;

class PagamentoTipoFormRequestRenderEntradaComParcelamento extends PagamentoTipoFormRequestRenderBase
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->buildRulesFromTipoPagamento(PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value);
    }

    // /**
    //  * Get the validation rules that apply to the request.
    //  *
    //  * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    //  */
    // public function rules(): array
    // {
    //     $pagamentoTipo = PagamentoTipo::find(PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value);
    //     $rules = parent::rules();
    //     foreach ($pagamentoTipo->campos_obrigatorios as $value) {
    //         if ($value['nome'] == 'valor_total') {
    //             $value['form_request_rule'] = str_replace('min:1', "min:" . (request('parcela_quantidade') * 1) + request('entrada_valor'), $value['form_request_rule']);
    //         }
    //         $rules[$value['nome']] = $value['form_request_rule'];
    //     }
    //     return $rules;
    // }

}
