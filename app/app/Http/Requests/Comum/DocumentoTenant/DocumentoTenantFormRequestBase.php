<?php

namespace App\Http\Requests\Comum\DocumentoTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            'nome' => 'required|string|min:3',
            'conteudo' => 'required|array',
            'observacao' => 'nullable|string',
            'documento_modelo_tenant_id' => 'required|uuid',
        ];

        $this->converterConteudoParaArray();

        return $rules;
    }

    /**
     * Converte o conteudo para um array para que possa ser validado pelas
     * regras de validação do Laravel. É enviado como uma string json para manter os atributos de formatação do quill.
     *
     * @return void
     */
    private function converterConteudoParaArray()
    {
        $this->merge(['conteudo' => json_decode($this->input('conteudo'), true)]);
    }
}
