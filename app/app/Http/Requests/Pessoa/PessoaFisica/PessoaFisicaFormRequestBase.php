<?php

namespace App\Http\Requests\Pessoa\PessoaFisica;

use App\Helpers\DocumentoTipoHelper;
use App\Http\Requests\BaseFormRequest;

class PessoaFisicaFormRequestBase extends BaseFormRequest
{

    public function rules()
    {
         $rules =  [
            'nome' => 'required|string|min:3',
            'mae' => 'nullable|string',
            'pai' => 'nullable|string',
            'nascimento_data' => 'nullable|date',
            'nascimento_cidade' => 'nullable|string',
            'nascimento_estado' => 'nullable|string',
            'nacionalidade' => 'nullable|string',
            'estado_civil_id' => 'nullable|uuid',
            'escolaridade_id' => 'nullable|uuid',
            'profissao' => 'nullable|string',
            'sexo_id' => 'nullable|uuid',
            'observacao' => 'nullable|string',
            'ativo_bln' => 'nullable|boolean',

            'documentos' => 'nullable|array',
            'documentos.*.id' => 'nullable|uuid',
            'documentos.*.documento_tipo_tenant_id' => 'required|uuid',

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

        // Gera as regras dinâmicas para documentos
        $documentos = $this->input('documentos', []);
        $rules = array_merge($rules, DocumentoTipoHelper::montarRegrasArrayTodosDocumentos($documentos));

        return $rules;
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

            'user.name' => 'nome de exibição',
            'user.email' => 'email',
            'user_domains.*.domain_id' => 'domínio',
        ];
    }
}
