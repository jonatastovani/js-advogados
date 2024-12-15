<?php

namespace App\Http\Requests\Tenant\DocumentoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoTipoTenantFormRequestShow extends BaseFormRequest
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
        $rules = ['modo_editar_bln' => 'nullable|boolean'];
        array_merge($rules, $this->rulesShowWithTrashed());
        return $rules;
    }
}
