<?php

namespace App\Http\Requests\Referencias\ContaSubtipo;

class ContaSubtipoFormRequestStore extends ContaSubtipoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
