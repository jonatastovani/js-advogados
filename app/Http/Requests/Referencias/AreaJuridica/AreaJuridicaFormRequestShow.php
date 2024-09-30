<?php

namespace App\Http\Requests\Referencias\AreaJuridica;

use App\Http\Requests\BaseFormRequest;

class AreaJuridicaFormRequestShow extends BaseFormRequest
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
            // Para casos de busca de registros que tenham sido excluídos
            'withTrashed' => 'nullable|boolean',
        ];
    }
}
