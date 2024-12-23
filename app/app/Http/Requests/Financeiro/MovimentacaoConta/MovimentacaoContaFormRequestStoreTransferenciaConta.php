<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Arr;

class MovimentacaoContaFormRequestStoreTransferenciaConta extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'conta_origem_id' => 'required|uuid',
            'data_transferencia' => 'required|date',
            'valor' => 'required|numeric|min:0.01',
            'conta_destino_id' => 'required|uuid',
            'observacao' => 'required|string',
        ];
    }

    protected function customMessages(): array
    {
        return [
            'valor.min' => 'O campo :attribute deve ser no mínimo :min.',
        ];
    }
}
