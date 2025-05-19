<?php

namespace App\Http\Requests\Validacao;

use App\Enums\DocumentoTipoEnum;
use App\Helpers\DocumentoTipoHelper;
use App\Http\Requests\BaseFormRequest;

class ValidacaoChavePixValidacaoFormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return DocumentoTipoHelper::montarRegrasDocumentoPorDocumentoTipo(DocumentoTipoEnum::CHAVE_PIX->value);
    }
}
