<?php

namespace App\Http\Requests\Tenant\DocumentoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoTipoTenantFormRequestIndexPorPessoaTipoAplicavel extends BaseFormRequest
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
        $rules = $this->rulesShowWithTrashed();
        $rules = array_merge($rules, [
            'pessoa_tipo_aplicavel' => 'required|array',
        ]);

        return $rules;
    }
}
