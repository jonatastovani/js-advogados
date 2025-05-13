<?php

namespace App\Http\Requests\Pessoa\PessoaJuridica;

use App\Http\Requests\BaseFormRequest;

class PessoaJuridicaFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'razao_social' => 'required|string|min:3',
            'nome_fantasia' => 'required|string',
            'natureza_juridica' => 'nullable|string',
            'data_fundacao' => 'nullable|date',
            'inscricao_estadual' => 'nullable|string',
            'inscricao_municipal' => 'nullable|string',
            'capital_social' => 'nullable|numeric',
            'cnae' => 'nullable|string',
            'responsavel_legal' => 'nullable|string',
            'cpf_responsavel' => 'nullable|string',
            'observacao' => 'nullable|string',
            'ativo_bln' => 'nullable|boolean',
            
            'documentos' => 'nullable|array',
            'documentos.*.id' => 'nullable|uuid',
            'documentos.*.documento_tipo_tenant_id' => 'required|uuid',
            'documentos.*.numero' => 'required|string',
            // 'documentos.*.campos_adicionais' => 'nullable|string',
            
            'perfis' => 'required|array|min:1',
            'perfis.*.id' => 'nullable|uuid',
            'perfis.*.perfil_tipo_id' => 'required|integer',
            'perfis.*.ativo_bln' => 'nullable|boolean',
            
            'enderecos' => 'nullable|array',
            'enderecos.*.id' => 'nullable|uuid',
            'enderecos.*.cep' => 'nullable|string',
            'enderecos.*.logradouro' => 'required|string',
            'enderecos.*.numero' => 'required|string',
            'enderecos.*.complemento' => 'nullable|string',
            'enderecos.*.bairro' => 'required|string',
            'enderecos.*.referencia' => 'nullable|string',
            'enderecos.*.cidade' => 'required|string',
            'enderecos.*.estado' => 'required|string',
            'enderecos.*.pais' => 'nullable|string',
            'enderecos.*.observacao' => 'nullable|string',
    ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'nascimento_data' => 'data de nascimento',
            'nascimento_cidade' => 'cidade de nascimento',
            'nascimento_estado' => 'estado de nascimento',
            'nacionalidade' => 'nacionalidade',
            'estado_civil_id' => 'estado civil',
            'escolaridade_id' => 'escolaridade',
            'sexo_id' => 'sexo',
            'observacao' => 'observação',
            'ativo_bln' => 'ativo',
        ];
    }
}
