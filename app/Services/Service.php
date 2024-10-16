<?php

namespace App\Services;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\ValidationRecordsHelper;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ServiceLogTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

abstract class Service
{
    use ServiceLogTrait, CommonServiceMethodsTrait, CommonsConsultaServiceTrait;

    abstract protected function traducaoCampos(array $dados);

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->save();
            DB::commit();
            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        return $resource->toArray();
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->save();
            DB::commit();
            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->delete();
            DB::commit();
            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        $conditions = $options['conditions'] ?? null;
        if (!$conditions || !is_array($conditions)) {
            if (isset($requestData->uuid)) {
                $conditions = [(new $this->model)->getKeyName() => $requestData->uuid];
            } else {
                $conditions = [(new $this->model)->getKeyName() => $requestData->id];
            }
        }

        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, $conditions, !$withTrashed);

        if ($resource->count() == 0) {
            // Usa o método do trait para gerar o log e lançar a exceção
            return $this->gerarLogRecursoNaoEncontrado(
                404,
                $options['message'] ?? 'O recurso não foi encontrado.',
                $requestData,
            );
        }

        // Retorna somente um registro
        return $resource[0];
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model;
        }

        // Preenche os atributos da model com os dados do request e conforme os campos $fillable definido na model
        $resource->fill($requestData->toArray());

        return $resource;
    }

    protected function gerarLogExceptionErroSalvar(Exception $e)
    {
        // Se ocorrer algum erro, fazer o rollback da transação
        DB::rollBack();

        // Gerar um log
        $codigo = 422;
        $mensagem = "A requisição não pôde ser processada.";
        $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | Errors: " . json_encode($e->getMessage()));

        $response = RestResponse::createGenericResponse(['error' => $e->getMessage()], 422, $mensagem, $traceId);
        return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
    }
}
