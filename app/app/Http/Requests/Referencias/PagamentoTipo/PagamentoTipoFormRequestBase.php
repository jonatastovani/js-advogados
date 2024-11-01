<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

use App\Http\Requests\BaseFormRequest;

class PagamentoTipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'required|string|min:3',
            'configuracao' => 'required|array',
            'configuracao.campos_obrigatorios' => 'required|array',
            'configuracao.campos_obrigatorios.*.nome' => 'required|string',
            'configuracao.campos_obrigatorios.*.nome_exibir' => 'required|string',
            'configuracao.helpers' => 'required|array',
            'configuracao.helpers.class' => 'required|string',
            'configuracao.helpers.endpoint_api' => 'required|string',
            'ativo_bln' => 'required|boolean',
        ];
    }

    // protected function customAttributeNames(): array
    // {
    //     return [
    //         'descricao' => 'descrição',
    //         'pagamentotipo_subtipo_id' => 'subtipo da pagamentotipo',
    //         'pagamentotipo_status_id' => 'status da pagamentotipo',
    //     ];
    // }
}
