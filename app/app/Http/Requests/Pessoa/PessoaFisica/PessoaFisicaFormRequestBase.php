<?php

namespace App\Http\Requests\Pessoa\PessoaFisica;

use App\Http\Requests\BaseFormRequest;

class PessoaFisicaFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'mae' => 'nullable|string',
            'pai' => 'nullable|string',
            'nascimento_data' => 'nullable|date',
            'nascimento_cidade' => 'nullable|string',
            'nascimento_estado' => 'nullable|string',
            'nacionalidade' => 'nullable|string',
            'estado_civil_id' => 'nullable|uuid',
            'escolaridade_id' => 'nullable|uuid',
            'sexo_id' => 'nullable|uuid',
            'observacao' => 'nullable|string',
            'ativo_bln' => 'nullable|boolean',
            'pessoa_perfil_tipo_id' => 'required|integer',
            'documentos' => 'nullable|array',
            'documentos.*.id' => 'nullable|uuid',
            'documentos.*.documento_tipo_tenant_id' => 'required|uuid',
            'documentos.*.numero' => 'required|string',
            // 'documentos.*.campos_adicionais' => 'nullable|string',
            'perfis' => 'nullable|array',
            'perfis.*.id' => 'nullable|uuid',
            'perfis.*.perfil_tipo_id' => 'required|integer',
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
            'user.username' => 'nome de usuário',
            'user.password' => 'senha',
            'user_domains.*.domain_id' => 'domínio',
        ];
    }
}
