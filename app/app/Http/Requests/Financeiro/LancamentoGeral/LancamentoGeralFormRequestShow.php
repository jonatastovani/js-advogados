<?php

namespace App\Http\Requests\Financeiro\LancamentoGeral;

use App\Http\Requests\BaseFormRequest;

class LancamentoGeralFormRequestShow extends BaseFormRequest
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
        $rules = [
            'lancamento_uuid' => 'nullable|uuid',
        ];

        return array_merge($this->rulesShowWithTrashed(), $rules);
    }
}
