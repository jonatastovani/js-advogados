<?php

namespace App\Http\Requests\Referencias\AreaJuridica;

use App\Http\Requests\BaseFormRequest;

class AreaJuridicaFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
        ];
    }

}
