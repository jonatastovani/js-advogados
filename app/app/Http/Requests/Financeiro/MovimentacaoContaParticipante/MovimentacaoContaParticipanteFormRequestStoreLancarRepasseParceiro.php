<?php

namespace App\Http\Requests\Financeiro\MovimentacaoContaParticipante;

use App\Enums\PessoaPerfilTipoEnum;
use App\Http\Requests\BaseFormRequest;

class MovimentacaoContaParticipanteFormRequestStoreLancarRepasseParceiro extends BaseFormRequest
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
        return [
            'perfil_tipo_id' => 'required|integer',
            // Requer o campo somente se perfil_tipo_id NÃO for EMPRESA
            'conta_movimentar' => [
                'required_if:perfil_tipo_id,!' . PessoaPerfilTipoEnum::EMPRESA->value,
                'string',
                'in:conta_debito,conta_origem',
            ],
            'conta_debito_id' => 'nullable|required_if:conta_movimentar,conta_debito|uuid',
            'participacoes' => 'required|array|min:1',
            'participacoes.*' => 'required|uuid',
        ];
    }
}
