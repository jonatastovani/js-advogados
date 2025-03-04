<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

use App\Common\RestResponse;
use App\Enums\DocumentoModeloTipoEnum;
use App\Helpers\LogHelper;
use App\Http\Requests\BaseFormRequest;
use App\Models\Referencias\DocumentoModeloTipo;

class DocumentoModeloTenantFormRequestRenderObjetos extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = [
            'objetos' => 'required|array',
            'objetos.*.identificador' => 'required|string',
            'objetos.*.id' => 'required|uuid',
        ];

        return $rules;
    }
}
