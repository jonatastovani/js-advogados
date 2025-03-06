<?php

namespace App\Services\Comum;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Comum\DocumentoTenant;
use App\Models\Servico\Servico;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class DocumentoTenantService extends Service
{

    public function __construct(
        DocumentoTenant $model,

        public Servico $modelServico,
    ) {
        parent::__construct($model);
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_titulo'] (mapeado para '[tableAsName].titulo')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();

        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function indexPadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resource = $this->model::with($this->loadFull())
            ->where('parent_type', $modelParent->getMorphClass())
            ->where('parent_id', $idParent)->get();
        return $resource->toArray();
    }

    public function indexServico(Fluent $requestData)
    {
        return $this->indexPadrao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    public function storePadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdatePadrao($requestData, $idParent, $modelParent);

        try {
            return DB::transaction(function () use ($resource, $idParent, $modelParent) {

                $resource->parent_id = $idParent;
                $resource->parent_type = $modelParent->getMorphClass();
                $resource->save();

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function storeServico(Fluent $requestData)
    {
        return $this->storePadrao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdatePadrao(Fluent $requestData, string $idParent, Model $modelParent, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, [
            'nome' => $requestData->nome,
            'parent_id' => $idParent,
            'parent_type' => $modelParent->getMorphClass(),
        ], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este documento, neste local, já existe.', $requestData->toArray());

            RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        /** @var DocumentoTenant  */
        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;
        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function loadFull($options = []): array
    {
        return [
            'documento_modelo_tenant',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
