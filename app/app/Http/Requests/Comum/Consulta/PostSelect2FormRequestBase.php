<?php

namespace App\Http\Requests\Comum\Consulta;

use App\Http\Requests\BaseFormRequest;

class PostSelect2FormRequestBase extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'text' => 'required|string|min:3',
        ];
    }
}
