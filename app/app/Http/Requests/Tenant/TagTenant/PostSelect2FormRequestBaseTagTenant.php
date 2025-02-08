<?php

namespace App\Http\Requests\Tenant\TagTenant;

use App\Enums\TagTipoTenantEnum;
use App\Http\Requests\Comum\Consulta\PostSelect2FormRequestBase;

class PostSelect2FormRequestBaseTagTenant extends PostSelect2FormRequestBase
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
        $tiposPermitidos = collect(TagTipoTenantEnum::toArray())->values()->implode(',');
        return array_merge(
            parent::rules(),
            [
                'tipo' => "required|string|in:{$tiposPermitidos}",
            ]
        );
    }
}
