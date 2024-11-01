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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pagamentoTipo = PagamentoTipo::find(PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value);
        $rules = parent::rules();
        foreach ($pagamentoTipo->configuracao['campos_obrigatorios'] as $value) {
            if ($value['nome'] == 'valor_total') {
                $value['formRequestRule'] = str_replace('min:0.01', "min:" . (request('parcela_quantidade') * 0.01) + request('entrada_valor'), $value['formRequestRule']);
            }
            $rules[$value['nome']] = $value['formRequestRule'];
        }
        return $rules;
    }
}
