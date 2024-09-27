<?php

namespace App\Http\Requests\Referencias\AreaJuridica;

class AreaJuridicaFormRequestUpdate extends AreaJuridicaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
