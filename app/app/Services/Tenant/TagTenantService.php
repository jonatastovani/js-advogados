<?php

namespace App\Services\Tenant;

use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\TagTenant;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class TagTenantService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(TagTenant $model)
    {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->where('tipo', $requestData->tipo)->get();
        return $resource->toArray();
    }

    public function select2(Fluent $requestData)
    {
        $dados = new Fluent([
            'camposFiltros' => ['nome', 'descricao'],
            'retornarQuery' => true,
        ]);

        $query =  $this->executaConsultaSelect2($requestData, $dados);
        $query->where('tipo', $requestData->tipo);

        // Definindo o campo de texto dinamicamente ou usando um padrão
        $campoTexto = $dados->campoTexto ?? 'nome'; // O padrão é 'nome'

        // Definir o limite de resultados (25 como padrão)
        $limite = $dados->limite ?? 25;

        // Executa a consulta com o limite e formata os resultados
        return $this->formatSelect2Results($query->limit($limite)->get(), $campoTexto)
            ->toArray();
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_nome'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_nome'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome, 'tipo' => $requestData->tipo], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta tag já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model();
        }

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function validacaoRecurso(string $id, Fluent $arrayErrors, array $options = []): Fluent
    {
        $validacaoTag = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $id]);
        if (!$validacaoTag->count()) {
            $arrayErrors["tag_{$id}"] = LogHelper::gerarLogDinamico(404, 'A Tag informada não existe ou foi excluída.', new Fluent(request()->all()))->error;
        }
        return new Fluent([
            'arrayErrors' => $arrayErrors,
            'resource' => $validacaoTag,
        ]);
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Tag não foi encontrada.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
