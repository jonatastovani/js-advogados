<?php

namespace App\Http\Requests\Tenant\TagTenant;

use App\Enums\TagTipoTenantEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Facades\Log;

class TagTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        $tiposPermitidos = collect(TagTipoTenantEnum::toArray())->values()->implode(',');
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'tipo' => "required|string|in:{$tiposPermitidos}",
        ];
    }
}
