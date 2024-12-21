<?php

namespace App\Traits;

use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

trait PessoaDocumentosMethodsTrait
{
   
    public function atualizarDocumentosEnviados($resource, $documentosExistentes, $documentosEnviados, $options = [])
    {
        // IDs dos documentos já salvos
        $existingDocumentos = collect($documentosExistentes)->pluck('id')->toArray();
        // IDs enviados (exclui novos documentos sem ID)
        $submittedDocumentosIds = collect($documentosEnviados)->pluck('id')->filter()->toArray();

        // Documentos ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingDocumentos, $submittedDocumentosIds);
        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $documentoDelete = $this->modelPessoaDocumento::find($id);
                if ($documentoDelete) {
                    $documentoDelete->delete();
                }
            }
        }

        foreach ($documentosEnviados as $documento) {

            if ($documento->id) {
                $documentoUpdate = $this->modelPessoaDocumento::find($documento->id);
                $documentoUpdate->fill($documento->toArray());
            } else {
                $documentoUpdate = $documento;
                $documentoUpdate->pessoa_id = $resource->pessoa->id;
            }

            $documentoUpdate->save();
        }
    }

    protected function verificacaoDocumentos(Fluent $requestData, Model $resource, Fluent $arrayErrors): Fluent
    {
        $documentosData = $requestData->documentos;
        $documentos = [];
        foreach ($documentosData as $documento) {
            $documento = new Fluent($documento);

            //Verifica se o tipo de registro de participação informado existe
            $validacaoDocumentoTipoTenantId = ValidationRecordsHelper::validateRecord($this->modelDocumentoTipoTenant::class, ['id' => $documento->documento_tipo_tenant_id]);
            if (!$validacaoDocumentoTipoTenantId->count()) {
                $arrayErrors["documento_tipo_tenant_id_{$documento->documento_tipo_tenant_id}"] = LogHelper::gerarLogDinamico(404, 'O tipo de documento informado não existe.', $requestData)->error;
            } else {

                $documentoTipoTenant = $validacaoDocumentoTipoTenantId->first()->load('documento_tipo');

                // Verifica se a classe existe, se não existir é porque não precisa de validação
                if (
                    isset($documentoTipoTenant['documento_tipo']['configuracao']['helper']['class']) &&
                    class_exists($documentoTipoTenant['documento_tipo']['configuracao']['helper']['class'])
                ) {
                    $helperClass = $documentoTipoTenant['documento_tipo']['configuracao']['helper']['class'];

                    // Verifica se o método 'executa' existe na classe
                    if (method_exists($helperClass, 'executa')) {
                        // Instancia a classe
                        $helperInstance = new $helperClass();

                        // Chama o método 'executa'
                        if (!$helperInstance::executa($documento->numero)) {
                            $arrayErrors["documento_numero_{$documento->numero}"] = LogHelper::gerarLogDinamico(404, 'O documento informado é inválido.', $requestData)->error;
                        };
                    } else {
                        // Lida com o caso onde o método não existe
                        // Por exemplo, lancar uma excecao ou registrar um log
                        throw new Exception("O método 'executa' não existe na classe {$helperClass}.");
                    }
                }

                // Verifica se o documento já existe para outra pessoa (duplicidade de cadastro)
                $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->modelPessoaDocumento::class, ['numero' => $documento->numero, 'documento_tipo_tenant_id' => $documento->documento_tipo_tenant_id], $documento->id ?? null);
                if ($validacaoRecursoExistente->count()) {
                    $arrayErrors->{"documento_{$documento->numero}"} = LogHelper::gerarLogDinamico(404, "O documento {$documentoTipoTenant['nome']} com número {$documento->numero} já existe cadastrado para outra pessoa.", $requestData)->error;
                }

                $newDocumento = new $this->modelPessoaDocumento;
                $newDocumento->fill($documento->toArray());
                array_push($documentos, $newDocumento);
            }
        }

        $retorno = new Fluent();
        $retorno->documentos = $documentos;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }
}
