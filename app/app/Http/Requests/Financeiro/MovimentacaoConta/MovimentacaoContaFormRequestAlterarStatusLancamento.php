<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Http\Requests\BaseFormRequest;

class MovimentacaoContaFormRequestAlterarStatusLancamento extends BaseFormRequest
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
            'lancamento_id' => 'required|uuid',
            'status_id' => 'required|integer',
        ];
    }
}
