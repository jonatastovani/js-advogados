<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

use App\Common\RestResponse;
use App\Enums\DocumentoModeloTipoEnum;
use App\Helpers\LogHelper;
use App\Http\Requests\BaseFormRequest;
use App\Models\Referencias\DocumentoModeloTipo;

class DocumentoModeloTenantFormRequestVerificacaoDocumentoEmCriacao extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = [
            'conteudo' => 'required|array',
            'documento_modelo_tipo_id' => 'required|integer',
            'objetos' => 'nullable|array',
        ];

        // // Busca o modelo para verificar os campos que cada modelo terá
        // $verificaDocumentoModeloTipo = function ($documentoModeloTipoId) {

        //     if (!$documentoModeloTipoId) {
        //         $log = LogHelper::gerarLogDinamico('404', 'Tipo de Modelo de Decumento não informado. Consulte o desenvolvedor.', $this);
        //         return RestResponse::createErrorResponse(404, $log->error, $log->trace_id)->throwResponse();
        //     }

        //     $consulta =  DocumentoModeloTipo::find($documentoModeloTipoId);

        //     if (!$consulta) {
        //         return RestResponse::createErrorResponse(404, 'Tipo de Pagamento do Tenant não encontrado.')->throwResponse();
        //     }
        //     return $consulta;
        // };

        // if ($this->has('documento_modelo_tipo_id')) {
        //     // Obtém o valor de 'documento_modelo_tipo_id' da requisição
        //     $documentoModeloTipo = $verificaDocumentoModeloTipo($this->input('documento_modelo_tipo_id'));

        //     if ($documentoModeloTipo->id == DocumentoModeloTipoEnum::SERVICO->value) {

        //         // Define as regras de acordo com o tipo de pagamento
        //         foreach ($documentoModeloTipo->campos_obrigatorios as $value) {
        //             $rules[$value['nome']] = $value['form_request_rule'];
        //         }
        //     }
        // }

        return $rules;
    }
}
