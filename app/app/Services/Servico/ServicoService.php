<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\AreaJuridicaTenant;
use App\Models\Servico\Servico;
use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

class ServicoService extends Service
{
    public function __construct(public Servico $model) {}

    public function postConsultaFiltros(Fluent $requestData)
    {
        $query = $this->consultaSimplesComFiltros($requestData);
        $query->with($this->loadFull());
        return $query->paginate($requestData->perPage ?? 25)->toArray();
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
        $permissionAsName = $this->model::getTableAsName();
        $arrayAliasCampos = [
            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $permissionAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load($this->loadFull());
        return $resource->toArray();
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        $checkDeletedAlteracaoAreaJuridicaTenant = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->area_juridica_id == $requestData->area_juridica_id) {
                $checkDeletedAlteracaoAreaJuridicaTenant = false;
            }
        } else {
            $resource = new $this->model();
        }

        //Verifica se a área jurídica informada existe
        $validacaoAreaJuridicaTenantId = ValidationRecordsHelper::validateRecord(AreaJuridicaTenant::class, ['id' => $requestData->area_juridica_id], $checkDeletedAlteracaoAreaJuridicaTenant);
        if (!$validacaoAreaJuridicaTenantId->count()) {
            $arrayErrors->area_juridica_id = LogHelper::gerarLogDinamico(404, 'A Área Jurídica informada não existe ou foi excluída.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->titulo = $requestData->titulo;
        $resource->descricao = $requestData->descricao;
        $resource->area_juridica_id = $requestData->area_juridica_id;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge(['message' => 'O Serviço não foi encontrado.'], $options));
    }

    private function loadFull(): array
    {
        return [
            'area_juridica',
            'anotacao',
            'pagamento.pagamento_tipo_tenant.pagamento_tipo',
            'pagamento.conta',
            'pagamento.lancamentos.status',
            'pagamento.lancamentos.conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
            'pagamento.participantes.participacao_tipo',
            'pagamento.participantes.integrantes.referencia.perfil_tipo',
            'pagamento.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'pagamento.participantes.referencia.perfil_tipo',
            'pagamento.participantes.referencia.pessoa.pessoa_dados',
            'pagamento.participantes.participacao_registro_tipo',
            'pagamento.lancamentos.participantes.participacao_tipo',
            'pagamento.lancamentos.participantes.integrantes.referencia.perfil_tipo',
            'pagamento.lancamentos.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'pagamento.lancamentos.participantes.referencia.perfil_tipo',
            'pagamento.lancamentos.participantes.referencia.pessoa.pessoa_dados',
            'pagamento.lancamentos.participantes.participacao_registro_tipo',
      ];
    }

    public function getRelatorioValores(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData, ['conditions' => ['id' => $requestData->servico_uuid]]);
        $data = new Fluent();
        $data->total_aguardando = $resource->total_aguardando;
        $data->total_inadimplente = $resource->total_inadimplente;
        $data->total_liquidado = $resource->total_liquidado;
        $data->valor_servico = $resource->valor_servico;
        return $data->toArray();
    }


    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
