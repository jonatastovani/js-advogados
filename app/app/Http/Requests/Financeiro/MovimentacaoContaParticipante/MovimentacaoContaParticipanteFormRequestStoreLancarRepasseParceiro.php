<?php

namespace App\Http\Requests\Financeiro\MovimentacaoContaParticipante;

use App\Http\Requests\BaseFormRequest;

class MovimentacaoContaParticipanteFormRequestStoreLancarRepasseParceiro extends BaseFormRequest
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
        return [
            'movimentacoes' => 'required|array|min:1',
            'movimentacoes.*' => 'required|uuid',
        ];
    }
}
