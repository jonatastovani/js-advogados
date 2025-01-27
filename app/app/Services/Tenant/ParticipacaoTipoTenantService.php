<?php

namespace App\Services\Tenant;

use App\Common\RestResponse;
use App\Enums\ParticipacaoTipoTenantConfiguracaoTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\ParticipacaoTipoTenant;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class ParticipacaoTipoTenantService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(ParticipacaoTipoTenant $model)
    {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {

        $resource = $this->model->where(function ($query) use ($requestData) {
            $query->where('configuracao->tipo', $requestData->configuracao_tipo);
        })->get();

        return $resource->toArray();
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosTexto($filtrosData['query'], $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarScopesPadrao($query, null, $options);

        // Filtrar a tipo de participação oculto para usuários
        $query->where(function ($query) use ($requestData) {
            $query->where('configuracao->tipo', $requestData->configuracao_tipo);
        });

        $query = $this->aplicarOrdenacoes($query, $requestData, $options);
        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    // public function select2(Request $request)
    // {
    //     $dados = new Fluent([
    //         'camposFiltros' => ['nome', 'descricao'],
    //     ]);

    //     return $this->executaConsultaSelect2($request, $dados);
    // }

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
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, [
            'nome' => $requestData->nome,
            'configuracao->tipo' => $requestData->configuracao['tipo'],
        ], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este tipo de participação já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(409, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model();
        if ($resource->bloqueado_para_usuario_comum && $resource->bloqueado_para_usuario_comum == true) {
            $arrayErrors =  LogHelper::gerarLogDinamico(422, 'A edição deste tipo de Participação é somente para administradores.', $requestData->toArray());
            return RestResponse::createErrorResponse(422, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resource->nome = $requestData->nome;
        $resource->descricao = $requestData->descricao;
        $resource->tipo = $requestData->configuracao['tipo'];

        return $resource;
    }

    public function getParticipacaoEmpresaLancamentoGeral()
    {
        $resource = ParticipacaoTipoTenant::whereJsonContains('configuracao->tag', 'participacao_empresa_movimentacao')->where('configuracao->tipo', ParticipacaoTipoTenantConfiguracaoTipoEnum::LANCAMENTO_GERAL)->first();
        if (!$resource) {
            throw new Exception('Tipo de Participação da empresa não foi configurada. Favor consultar o desenvolvedor.', 404);
        }

        return $resource->toArray();
    }
    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O Tipo de Participação não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
