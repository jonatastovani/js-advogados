<?php

namespace App\Services\Pessoa;

use App\Common\CommonsFunctions;
use App\Enums\PessoaPerfilTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Auth\UserTenantDomain;
use App\Models\Comum\Endereco;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaDocumento;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Tenant\DocumentoTipoTenant;
use App\Models\Tenant\EscolaridadeTenant;
use App\Models\Tenant\EstadoCivilTenant;
use App\Models\Tenant\SexoTenant;
use App\Services\Service;
use App\Traits\EnderecosMethodsTrait;
use App\Traits\PessoaDocumentosMethodsTrait;
use App\Traits\PessoaFisicaUsuarioMethodsTrait;
use App\Traits\PessoaPerfilMethodsTrait;
use App\Traits\UserDomainMethodsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class PessoaFisicaService extends Service
{
    use PessoaDocumentosMethodsTrait,
        PessoaPerfilMethodsTrait,
        UserDomainMethodsTrait,
        PessoaFisicaUsuarioMethodsTrait,
        EnderecosMethodsTrait;

    public function __construct(
        PessoaFisica $model,
        public Pessoa $modelPessoa,
        public PessoaPerfil $modelPessoaPerfil,
        public PessoaDocumento $modelPessoaDocumento,
        public DocumentoTipoTenant $modelDocumentoTipoTenant,
        public UserTenantDomain $modelUserTenantDomain,
        public Endereco $modelEndereco,
    ) {
        parent::__construct($model);
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
        $modalPessoaDocumentoAsName = $this->modelPessoaDocumento->getTableAsName();

        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_mae' => isset($aliasCampos['col_mae']) ? $aliasCampos['col_mae'] : $modelAsName,
            'col_pai' => isset($aliasCampos['col_pai']) ? $aliasCampos['col_pai'] : $modelAsName,

            'col_documento' => isset($aliasCampos['col_documento']) ? $aliasCampos['col_documento'] : $modalPessoaDocumentoAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_mae' => ['campo' => $arrayAliasCampos['col_mae'] . '.mae'],
            'col_pai' => ['campo' => $arrayAliasCampos['col_pai'] . '.pai'],

            // 'col_documento' => ['campo' => $arrayAliasCampos['col_documento'] . '.numero'],
            'col_documento' => ['campo' => $arrayAliasCampos['col_documento'] . '.numero', 'tratamento' => ['personalizado' => 'documento']],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('nome')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => "{$this->model->getTableAsName()}.nome", 'direcao' => 'asc']]
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

        $query->whereIn("{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", $requestData->perfis_busca);

        // Filtrar somente os perfis ativos, caso não seja enviado o parâmetro include_perfis_inativos
        if (!$requestData->include_perfis_inativos) {
            $query->where("{$this->modelPessoaPerfil->getTableAsName()}.ativo_bln", true);
        }

        if (isset($requestData->ativo_bln)) {
            $query->where("{$this->model->getTableAsName()}.ativo_bln", $requestData->ativo_bln);
        }

        return $query;
    }

    protected function carregarRelacionamentos(Builder $query, Fluent $requestData, array $options = [])
    {
        $relationshipsAppend = [];
        if (in_array(PessoaPerfilTipoEnum::USUARIO->value, $requestData->perfis_busca)) {
            $relationshipsAppend[] = 'pessoa.perfil_usuario.user.user_tenant_domains';
        }

        if ($options['loadFull'] ?? false) {
            $query->with($options['loadFull']);
        } else {
            if (method_exists($this, 'loadFull') && is_array($this->loadFull())) {
                $query->with(array_merge(
                    $relationshipsAppend,
                    $this->loadFull($options)
                ));
            }
        }
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($requestData->perPage ?? 25);

        return $paginator->toArray();
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {
            return DB::transaction(function () use ($resource, $requestData) {

                $documentos = $resource->documentos;
                unset($resource->documentos);

                $perfis = $resource->perfis;
                unset($resource->perfis);

                $user = null;
                if ($resource->user) {
                    $user = $resource->user;
                    unset($resource->user);
                }

                $userDomains = null;
                // Somente se for update do tipo usuário para verificar domínios
                if (in_array(PessoaPerfilTipoEnum::USUARIO->value, collect($perfis)->pluck('perfil_tipo_id')->toArray())) {
                    if ($resource->userDomains) {
                        $userDomains = $resource->userDomains;
                    }
                }
                unset($resource->userDomains);

                $enderecos = $resource->enderecos;
                unset($resource->enderecos);

                //Salva os dados da Pessoa Jurídica
                $resource->save();

                // Salva os dados da Pessoa
                $pessoa = new $this->modelPessoa;
                $pessoa->pessoa_dados_type = $this->model->getMorphClass();
                $pessoa->pessoa_dados_id = $resource->id;
                $pessoa->save();

                if (collect($documentos)->isNotEmpty()) {
                    // Fazer salvamento dos documentos
                    foreach ($documentos as $documento) {
                        $documento->pessoa_id = $pessoa->id;
                        $documento->save();
                    }
                }

                // Fazer salvamento dos documentos
                $this->atualizarDocumentosEnviados($resource, $resource->pessoa->documentos, $documentos);

                // Fazer salvamento dos perfis
                $this->atualizarPerfisEnviados($resource, $resource->pessoa->pessoa_perfil, $perfis);

                // $perfilUsuario = null;
                // // Fazer salvamento dos perfis
                // foreach ($perfis as $perfil) {
                //     $perfil->pessoa_id = $pessoa->id;
                //     $perfil->save();
                //     if ($perfil->perfil_tipo_id == PessoaPerfilTipoEnum::USUARIO->value) {
                //         $perfilUsuario = $perfil;
                //     }
                // }

                $perfilUsuario = $resource->pessoa->perfil_usuario;

                // Se foi enviado user, então é um salvamento de dados de usuário, logo, se cria o usuário e depois se faz a verificação de salvamento de domínios 
                if ($user && $perfilUsuario) {
                    $user->pessoa_perfil_id = $perfilUsuario->id;
                    $user->tenant_id = tenant('id');
                    $user = $this->atualizarOuCriarUsuarioEnviado($resource, $user);

                    if (collect($userDomains)->isNotEmpty()) {
                        // Fazer salvamento dos domínios
                        foreach ($userDomains as $domain) {
                            $domain->user_id = $user->id;
                            $domain->save();
                        }
                    }
                }

                // Fazer salvamento dos enderecos
                $this->atualizarEnderecosEnviados($resource, $resource->pessoa->enderecos, $enderecos);

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
            return DB::transaction(function () use ($resource, $requestData) {

                // Obter e remover do resource os documentos
                $documentos = $resource->documentos;
                unset($resource->documentos);

                $perfis = $resource->perfis;
                unset($resource->perfis);

                $user = null;
                if ($resource->user) {
                    $user = $resource->user;
                }
                unset($resource->user);

                $userDomains = [];
                $userDomainsExistentes = [];
                // Somente se for update do tipo usuário para alterar os domínios
                if (in_array(PessoaPerfilTipoEnum::USUARIO->value, collect($perfis)->pluck('perfil_tipo_id')->toArray())) {
                    if ($resource->userDomains) {
                        $userDomains = $resource->userDomains;
                    }
                    // Busca os dominios existentes
                    $userDomainsExistentes = $resource->pessoa->perfil_usuario->user->user_tenant_domains ?? [];
                }
                unset($resource->userDomains);

                // Obter e remover do resource os enderecos
                $enderecos = $resource->enderecos;
                unset($resource->enderecos);

                $resource->save();

                // Fazer salvamento dos documentos
                $this->atualizarDocumentosEnviados($resource, $resource->pessoa->documentos, $documentos);

                // Fazer salvamento dos perfis
                $this->atualizarPerfisEnviados($resource, $resource->pessoa->pessoa_perfil, $perfis);

                // Se for enviado dados de usuário
                if ($user) {
                    $user = $this->atualizarOuCriarUsuarioEnviado($resource, $user);

                    if ($userDomains || $userDomainsExistentes) {
                        // Fazer salvamento dos domínios
                        $this->atualizarUserDomainsEnviados($resource, $userDomainsExistentes, $userDomains, $user);
                    }
                }

                // Fazer salvamento dos enderecos
                $this->atualizarEnderecosEnviados($resource, $resource->pessoa->enderecos, $enderecos);

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

        // Busca ou cria o recurso principal
        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        // Validações comuns
        $arrayErrors = $this->validarRelacionamentosComuns($requestData, $arrayErrors);

        // Verifica e processa documentos
        $documentosProcessados = $this->verificacaoDocumentos($requestData, $resource, $arrayErrors);
        $resource->documentos = $documentosProcessados->documentos;
        $arrayErrors = $documentosProcessados->arrayErrors;

        // Verifica e processa perfis
        $perfisProcessados = $this->verificacaoPerfis($requestData, $resource, $arrayErrors);
        $resource->perfis = $perfisProcessados->perfis;
        $arrayErrors = $perfisProcessados->arrayErrors;

        // Verificação específica para tipo de perfil USUARIO
        if (in_array(PessoaPerfilTipoEnum::USUARIO->value, collect($resource->perfis)->pluck('perfil_tipo_id')->toArray())) {

            // Verifica e processa domínios
            $userDomainsProcessados = $this->verificacaoUserDomains($requestData, $resource, $arrayErrors);
            $resource->userDomains = $userDomainsProcessados->userDomains;
            $arrayErrors = $userDomainsProcessados->arrayErrors;

            // Verifica e processa o usuário
            $userProcessado = $this->verificarUsuario($requestData, $resource, $arrayErrors);
            $resource->user = $userProcessado->user;
            $arrayErrors = $userProcessado->arrayErrors;
        }

        // Verifica e processa enderecos
        $enderecosProcessados = $this->verificacaoEnderecos($requestData, $resource, $arrayErrors);
        $resource->enderecos = $enderecosProcessados->enderecos;
        $arrayErrors = $enderecosProcessados->arrayErrors;

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        // Preenche os dados do recurso
        $resource->fill($requestData->toArray());

        return $resource;
    }

    /**
     * Validações relacionadas a estado civil, escolaridade e sexo.
     */
    protected function validarRelacionamentosComuns(Fluent $requestData, Fluent $arrayErrors): Fluent
    {
        if ($requestData->estado_civil_id) {
            $validacao = ValidationRecordsHelper::validateRecord(EstadoCivilTenant::class, ['id' => $requestData->estado_civil_id]);
            if (!$validacao->count()) {
                $arrayErrors->estado_civil_id = LogHelper::gerarLogDinamico(404, 'O Estado Civil informado não existe.', $requestData)->error;
            }
        }

        if ($requestData->escolaridade_id) {
            $validacao = ValidationRecordsHelper::validateRecord(EscolaridadeTenant::class, ['id' => $requestData->escolaridade_id]);
            if (!$validacao->count()) {
                $arrayErrors->escolaridade_id = LogHelper::gerarLogDinamico(404, 'A Escolaridade informada não existe.', $requestData)->error;
            }
        }

        if ($requestData->sexo_id) {
            $validacao = ValidationRecordsHelper::validateRecord(SexoTenant::class, ['id' => $requestData->sexo_id]);
            if (!$validacao->count()) {
                $arrayErrors->sexo_id = LogHelper::gerarLogDinamico(404, 'O Sexo informado não existe.', $requestData)->error;
            }
        }

        return $arrayErrors;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Pessoa Física não foi encontrada.',
        ], $options));
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            'escolaridade',
            'estado_civil',
            'sexo',
        ];

        // Verifica se PessoaService está na lista de exclusão
        $classImport = PessoaService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
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
