<?php

namespace App\Http\Requests\Validacao;

use App\Http\Requests\BaseFormRequest;

class ValidacaoCPFValidacaoFormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'texto' => 'required|string',
        ];
    }
}
