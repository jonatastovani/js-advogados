<?php

namespace App\Http\Requests\Financeiro\MovimentacaoContaParticipante;

use App\Enums\PessoaPerfilTipoEnum;

class MovimentacaoContaParticipanteFormRequestStoreLancarRepasse extends PostConsultaFiltroFormRequestBalancoRepasse
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
        return array_merge(parent::rules(), [
            'perfil_tipo_id' => 'required|integer',
            // Requer o campo somente se perfil_tipo_id NÃO for EMPRESA
            'conta_movimentar' => [
                'required_if:perfil_tipo_id,!' . PessoaPerfilTipoEnum::EMPRESA->value,
                'string',
                'in:conta_debito,conta_origem',
            ],
            'conta_debito_id' => [
                'sometimes', // Valida apenas se o campo estiver presente
                'required_if:conta_movimentar,conta_debito',
                'uuid',
            ],
        ]);
    }

    // public function rules(): array
    // {
    //     return [
    //         'perfil_tipo_id' => 'required|integer',
    //         // Requer o campo somente se perfil_tipo_id NÃO for EMPRESA
    //         'conta_movimentar' => [
    //             'required_if:perfil_tipo_id,!' . PessoaPerfilTipoEnum::EMPRESA->value,
    //             'string',
    //             'in:conta_debito,conta_origem',
    //         ],
    //         'conta_debito_id' => [
    //             'sometimes', // Valida apenas se o campo estiver presente
    //             'required_if:conta_movimentar,conta_debito',
    //             'uuid',
    //         ],
    //         'participacoes' => 'required|array|min:1',
    //         'participacoes.*' => 'required|uuid',
    //     ];
    // }
}
