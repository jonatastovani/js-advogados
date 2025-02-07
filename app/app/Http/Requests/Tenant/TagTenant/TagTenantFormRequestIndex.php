<?php

namespace App\Http\Requests\Tenant\TagTenant;

use App\Http\Requests\BaseFormRequest;

class TagTenantFormRequestIndex extends BaseFormRequest
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
        return array_merge(
            $this->rulesShowWithTrashed(),
            [
                'tipo' => 'required|string|in:lancamento_geral',
            ]
        );
    }
}
