<?php

namespace App\Services\Pessoa;

use App\Common\CommonsFunctions;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaDocumento;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Tenant\DocumentoTipoTenant;
use App\Models\Tenant\EscolaridadeTenant;
use App\Models\Tenant\EstadoCivilTenant;
use App\Models\Tenant\SexoTenant;
use App\Services\Service;
use App\Traits\PessoaDocumentosMethodsTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class PessoaJuridicaService extends Service
{
    use PessoaDocumentosMethodsTrait;
    
    public function __construct(
        PessoaJuridica $model,
        public Pessoa $modelPessoa,
        public PessoaPerfil $modelPessoaPerfil,
        public PessoaDocumento $modelPessoaDocumento,
        public DocumentoTipoTenant $modelDocumentoTipoTenant,
    ) {
        parent::__construct($model);
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_razao_social'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $modalPessoaDocumentoAsName = $this->modelPessoaDocumento->getTableAsName();

        $arrayAliasCampos = [
            'col_razao_social' => isset($aliasCampos['col_razao_social']) ? $aliasCampos['col_razao_social'] : $modelAsName,
            'col_nome_fantasia' => isset($aliasCampos['col_nome_fantasia']) ? $aliasCampos['col_nome_fantasia'] : $modelAsName,
            'col_responsavel_legal' => isset($aliasCampos['col_responsavel_legal']) ? $aliasCampos['col_responsavel_legal'] : $modelAsName,

            'col_documento' => isset($aliasCampos['col_documento']) ? $aliasCampos['col_documento'] : $modalPessoaDocumentoAsName,
        ];

        $arrayCampos = [
            'col_razao_social' => ['campo' => $arrayAliasCampos['col_razao_social'] . '.razao_social'],
            'col_nome_fantasia' => ['campo' => $arrayAliasCampos['col_nome_fantasia'] . '.nome_fantasia'],
            'col_responsavel_legal' => ['campo' => $arrayAliasCampos['col_responsavel_legal'] . '.responsavel_legal'],

            'col_documento' => ['campo' => $arrayAliasCampos['col_documento'] . '.numero'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_razao_social'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('razao_social')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => "{$this->model->getTableAsName()}.razao_social", 'direcao' => 'asc']]
            );
        }
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('created_at')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => "{$this->model->getTableAsName()}.created_at", 'direcao' => 'asc']]
            );
        }

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, $options);

        $query->groupBy("{$this->model->getTableAsName()}.id");

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    /**
     * Aplica filtros específicos baseados nos campos de busca fornecidos.
     *
     * @param Builder $query Instância do query builder.
     * @param array $filtros Filtros fornecidos na requisição.
     * @param Fluent $requestData Dados da requisição.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query modificada com os joins e filtros específicos aplicados.
     */
    private function aplicarFiltrosEspecificos(Builder $query, $filtros, $requestData, array $options = [])
    {
        $blnDocumentoFiltro = in_array('col_documento', $filtros['campos_busca']);
        $query = $this->model::joinPessoaAPessoaPerfil($query);

        if ($blnDocumentoFiltro) {
            $query = $this->modelPessoa::joinPessoaDocumento($query);
        }

        if (isset($requestData->ativo_bln)) {
            $query->where("{$this->model->getTableAsName()}.ativo_bln", $requestData->ativo_bln);
        }

        $query->whereIn("{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", $requestData->perfis_busca);

        return $query;
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {
            return DB::transaction(function () use ($resource, $requestData) {

                $documentos = $resource->documentos;
                unset($resource->documentos);

                //Salva os dados da Pessoa Jurídica
                $resource->save();

                // Salva os dados da Pessoa
                $pessoa = new $this->modelPessoa;
                $pessoa->pessoa_dados_type = $this->model->getMorphClass();
                $pessoa->pessoa_dados_id = $resource->id;
                $pessoa->save();

                // Salva os dados do perfil
                $pessoaPerfil = new $this->modelPessoaPerfil;
                $pessoaPerfil->pessoa_id = $pessoa->id;
                $pessoaPerfil->perfil_tipo_id = $requestData->pessoa_perfil_tipo_id;
                $pessoaPerfil->save();

                // Fazer salvamento dos documentos
                foreach ($documentos as $documento) {
                    $documento->pessoa_id = $pessoa->id;
                    $documento->save();
                }

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        try {
            return DB::transaction(function () use ($resource) {

                // Obter e remover do resource os documentos
                $documentos = $resource->documentos;
                unset($resource->documentos);

                // Busca os documentos existentes
                $documentosExistentes = $resource->pessoa->documentos;

                $resource->save();

                // Fazer salvamento dos documentos
                $this->atualizarDocumentosEnviados($resource, $documentosExistentes, $documentos);

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        $documentosProcessados = $this->verificacaoDocumentos($requestData, $resource);
        $resource->documentos = $documentosProcessados->documentos;
        $arrayErrors = new Fluent(array_merge($arrayErrors->toArray(), $documentosProcessados->arrayErrors->toArray()));

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Pessoa Física não foi encontrada.',
        ], $options));
    }

    /**
     * Carrega os relacionamentos completos da service, aplicando manipulação dinâmica.
     *
     * @param array $options Opções para manipulação de relacionamentos.
     *     - 'withOutClass' (array|string|null): Lista de classes que não devem ser chamadas
     *       para evitar referências circulares.
     * @return array Array de relacionamentos manipulados.
     */
    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = (array)($options['withOutClass'] ?? []);

        $relationships = [];

        // Verifica se PessoaService está na lista de exclusão
        $classImport = PessoaService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(['withOutClass' => array_merge([self::class], $options)]),
                [
                    'addPrefix' => 'pessoa.', // Adiciona um prefixo aos relacionamentos externos
                    'removePrefix' => 'pessoa_dados',
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
