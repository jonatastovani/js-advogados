<?php

namespace App\Services\Pessoa;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PessoaTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\Pessoa;
use App\Models\Referencias\PessoaStatusTipo;
use App\Models\Referencias\PessoaSubtipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class PessoaService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(
        Pessoa $model,
        public PessoaFisicaService $pessoaFisicaService
    ) {
        parent::__construct($model);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->pessoaFisicaService->extrairFiltros($requestData, array_merge($options, [
            'arrayCamposSelect' => ['id']
        ]));
        $queryFisica = $this->pessoaFisicaService->aplicarFiltrosTexto($filtrosData['query'], $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $queryFisica->groupBy($this->pessoaFisicaService->model->getTableAsName() . '.id');
        $queryFisica = $this->aplicarScopesPadrao($queryFisica, $this->pessoaFisicaService->model, $options);
        $queryFisica = $this->pessoaFisicaService->aplicarOrdenacoes($queryFisica, $requestData, $options);

        $query = $this->model::whereIn('pessoa_dados_id', $queryFisica)->whereHas('pessoa_perfil', function ($q) use ($requestData) {
            $q->whereIn('perfil_tipo_id', $requestData->perfis_busca);
        });

        // adicionar chave para carregamento das pessoas físicas
        $options = array_merge($options, [
            'caseTipoPessoa' => PessoaTipoEnum::PESSOA_FISICA,
        ]);

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    public function postConsultaFiltrosJuridica(Fluent $requestData)
    {
        // $query = $this->consultaSimplesComFiltros($requestData);
        // $query->with($this->loadFull());
        // return $query->paginate($requestData->perPage ?? 25)->toArray();
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
            'col_mae' => isset($aliasCampos['col_mae']) ? $aliasCampos['col_mae'] : $modelAsName,
            'col_pai' => isset($aliasCampos['col_pai']) ? $aliasCampos['col_pai'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_mae' => ['campo' => $arrayAliasCampos['col_mae'] . '.mae'],
            'col_pai' => ['campo' => $arrayAliasCampos['col_pai'] . '.pai'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    // protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    // {
    //     $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome, 'domain_id' => DomainTenantResolver::$currentDomain->id], $id);
    //     if ($validacaoRecursoExistente->count() > 0) {
    //         $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta conta já existe.', $requestData->toArray());
    //         return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
    //     }

    //     $arrayErrors = new Fluent();

    //     $resource = null;
    //     $checkDeletedAlteracaoPessoaSubtipo = true;
    //     $checkDeletedAlteracaoPessoaStatus = true;
    //     if ($id) {
    //         $resource = $this->buscarRecurso($requestData);

    //         if ($resource->conta_subtipo_id == $requestData->conta_subtipo_id) {
    //             $checkDeletedAlteracaoPessoaSubtipo = false;
    //         }

    //         if ($resource->conta_status_id == $requestData->conta_status_id) {
    //             $checkDeletedAlteracaoPessoaStatus = false;
    //         }
    //     } else {
    //         $resource = new $this->model();
    //     }

    //     $validacaoPessoaSubtipoId = ValidationRecordsHelper::validateRecord(PessoaSubtipo::class, ['id' => $requestData->conta_subtipo_id], $checkDeletedAlteracaoPessoaSubtipo);
    //     if (!$validacaoPessoaSubtipoId->count()) {
    //         $arrayErrors->conta_subtipo_id = LogHelper::gerarLogDinamico(404, 'O subtipo da conta informado não existe ou foi excluído.', $requestData)->error;
    //     }

    //     $validacaoPessoaStatusId = ValidationRecordsHelper::validateRecord(PessoaStatusTipo::class, ['id' => $requestData->conta_status_id], $checkDeletedAlteracaoPessoaStatus);
    //     if (!$validacaoPessoaStatusId->count()) {
    //         $arrayErrors->conta_status_id = LogHelper::gerarLogDinamico(404, 'O valor de status para a conta não existe ou foi excluído.', $requestData)->error;
    //     }

    //     // Erros que impedem o processamento
    //     CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

    //     $resource->fill($requestData->toArray());

    //     return $resource;
    // }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Pessoa não foi encontrada.',
        ], $options));
    }

    /**
     * Carrega os relacionamentos completos para o serviço, aplicando manipulações dinâmicas
     * com base nas opções fornecidas. Este método ajusta os relacionamentos a serem carregados
     * dependendo do tipo de pessoa (Física ou Jurídica) e considera se a chamada é externa 
     * para evitar carregamentos duplicados ou redundantes.
     *
     * @param array $options Opções para manipulação de relacionamentos:
     *     - 'caseTipoPessoa' (PessoaTipoEnum|null): Define o tipo de pessoa para o carregamento
     *       específico. Pode ser Pessoa Física ou Jurídica. Se não for informado, aplica um 
     *       comportamento padrão.
     *     - 'withOutClass' (array|string|null): Classes que devem ser excluídas do carregamento
     *       de relacionamentos, útil para evitar referências circulares.
     *
     * @return array Retorna um array de relacionamentos manipulados.
     *
     * @throws Exception Se houver algum erro durante o carregamento dinâmico dos serviços.
     *
     * Lógica:
     * - Verifica o tipo de pessoa (Física ou Jurídica) e ajusta os relacionamentos com base
     *   no serviço correspondente (PessoaFisicaService ou PessoaJuridicaService).
     * - Se nenhum tipo de pessoa for especificado, adiciona o relacionamento genérico 'pessoa_dados'.
     * - Utiliza a função `mergeRelationships` para mesclar relacionamentos existentes com 
     *   os novos, aplicando prefixos onde necessário.
     *
     * Exemplo de Uso:
     * ```php
     * $service = new PessoaService();
     * $relationships = $service->loadFull([
     *     'caseTipoPessoa' => PessoaTipoEnum::PESSOA_FISICA,
     * ]);
     * ```
     */
    public function loadFull($options = []): array
    {
        // Tipo de pessoa enviado para o carregamento específico do tipo de pessoa
        $caseTipoPessoa = $options['caseTipoPessoa'] ?? null;

        // Função para carregar dados de Pessoa Física ou Jurídica dinamicamente
        $carregarPessoaPorTipo = function ($serviceTipoPessoa, $relationships) use ($options) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($serviceTipoPessoa)->loadFull(['withOutClass' => array_merge([self::class], $options)]),
                [
                    'addPrefix' => 'pessoa_dados.' // Adiciona um prefixo aos relacionamentos externos
                ]
            );

            return $relationships;
        };

        $relationships = [
            'pessoa_perfil.perfil_tipo',
        ];

        // Verifica o tipo de pessoa e ajusta os relacionamentos
        if ($caseTipoPessoa === PessoaTipoEnum::PESSOA_FISICA) {
            $relationships = $carregarPessoaPorTipo(PessoaFisicaService::class, $relationships);
        } elseif ($caseTipoPessoa === PessoaTipoEnum::PESSOA_JURIDICA) {
            $relationships = $carregarPessoaPorTipo(PessoaJuridicaService::class, $relationships);
        } else {
            $relationships = array_merge(
                $relationships,
                [
                    'pessoa_dados',
                ]
            );
        }

        return $relationships;
    }


    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
