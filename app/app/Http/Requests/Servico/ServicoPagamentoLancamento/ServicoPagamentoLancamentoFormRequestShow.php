<?php

namespace App\Http\Requests\Servico\ServicoPagamentoLancamento;

use App\Http\Requests\BaseFormRequest;

class ServicoPagamentoLancamentoFormRequestShow extends BaseFormRequest
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
            'pagamento_uuid' => 'nullable|uuid',
        ];

        return array_merge($this->rulesShowWithTrashed(), $rules);
    }
}
