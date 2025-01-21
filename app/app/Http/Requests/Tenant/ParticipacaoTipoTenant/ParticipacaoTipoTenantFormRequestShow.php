<?php

namespace App\Http\Requests\Tenant\ParticipacaoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class ParticipacaoTipoTenantFormRequestShow extends BaseFormRequest
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
        return $this->rulesShowWithTrashed();
    }
}
