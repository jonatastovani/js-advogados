<?php

namespace App\Http\Requests\Auth\Tenant;

use Illuminate\Support\Arr;

class TenantFormRequestUpdateCliente extends TenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return Arr::only(parent::rules(), [
            'name',
            // Pode mandar o sigla desta forma porque a trait VirtualColumn faz o encode e o decode dentro do json
            'sigla',
            'domains',
            'domains.*.id',
            'domains.*.name',
        ]);
    }
}
