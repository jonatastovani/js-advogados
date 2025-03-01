<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

use App\Common\RestResponse;
use App\Enums\DocumentoModeloTipoEnum;
use App\Helpers\LogHelper;
use App\Http\Requests\BaseFormRequest;
use App\Models\Referencias\DocumentoModeloTipo;
use Illuminate\Support\Facades\Log;

class DocumentoModeloTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'conteudo' => 'required|array',
            'ativo_bln' => 'nullable|boolean',
            'documento_modelo_tipo_id' => 'required|integer',
            'objetos' => 'nullable|array',
            'objetos.*.contador' => 'required|integer',
            'objetos.*.identificador' => 'required|string',
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
