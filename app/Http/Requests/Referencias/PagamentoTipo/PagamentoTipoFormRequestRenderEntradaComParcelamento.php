<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Enums\PagamentoTipoEnum;
use App\Http\Requests\BaseFormRequest;
use App\Models\Referencias\PagamentoTipo;

class PagamentoTipoFormRequestRenderEntradaComParcelamento extends BaseFormRequest
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
        $rules = ['conta_id' => 'required|uuid'];
        foreach ($pagamentoTipo->configuracao['campos_obrigatorios'] as $value) {
            $rules[$value['nome']] = $value['formRequestRule'];
        }
        return $rules;
    }
}
