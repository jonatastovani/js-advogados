<?php

namespace App\Services\Pessoa;

use App\Common\CommonsFunctions;
use App\Enums\PessoaPerfilTipoEnum;
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
use App\Traits\PessoaPerfilMethodsTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class PessoaJuridicaService extends Service
{
    use PessoaDocumentosMethodsTrait, PessoaPerfilMethodsTrait;

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
            return DB::transaction(function () use ($resource) {

                $documentos = $resource->documentos;
                unset($resource->documentos);

                $perfis = $resource->perfis;
                unset($resource->perfis);

                // $user = null;
                // if ($resource->user) {
                //     $user = $resource->user;
                //     unset($resource->user);
                // }

                // $userDomains = null;
                // if ($resource->userDomains) {
                //     $userDomains = $resource->userDomains;
                //     unset($resource->userDomains);
                // }

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

                // $perfilUsuario = null;
                // Fazer salvamento dos perfis
                foreach ($perfis as $perfil) {
                    $perfil->pessoa_id = $pessoa->id;
                    $perfil->save();
                    // if ($perfil->perfil_tipo_id == PessoaPerfilTipoEnum::USUARIO->value) {
                    //     $perfilUsuario = $perfil;
                    // }
                }

                // // Se foi enviado user, então é um salvamento de dados de usuário, logo, se cria o usuário e depois se faz a verificação de salvamento de domínios 
                // if ($user && $perfilUsuario) {
                //     $user->pessoa_perfil_id = $perfilUsuario->id;
                //     $user->tenant_id = tenant('id');
                //     $user->save();

                //     if (collect($userDomains)->isNotEmpty()) {
                //         // Fazer salvamento dos domínios
                //         foreach ($userDomains as $domain) {
                //             $domain->user_id = $user->id;
                //             $domain->save();
                //         }
                //     }
                // }

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

                // $user = null;
                // if ($resource->user) {
                //     $user = $resource->user;
                // }
                // unset($resource->user);

                // $userDomains = [];
                // $userDomainsExistentes = [];
                // // Somente se for update do tipo usuário para alterar os domínios
                // if ($requestData->pessoa_perfil_tipo_id === PessoaPerfilTipoEnum::USUARIO->value) {
                //     if ($resource->userDomains) {
                //         $userDomains = $resource->userDomains;
                //     }
                //     // Busca os dominios existentes
                //     $userDomainsExistentes = $resource->pessoa->perfil_usuario->user->user_tenant_domains ?? [];
                //     unset($resource->userDomains);
                // }

                // Busca os documentos existentes
                $documentosExistentes = $resource->pessoa->documentos;
                // Busca os perfis existentes
                $perfisExistentes = $resource->pessoa->pessoa_perfil;

                $resource->save();

                // Fazer salvamento dos documentos
                $this->atualizarDocumentosEnviados($resource, $documentosExistentes, $documentos);

                // Fazer salvamento dos perfis
                $this->atualizarPerfisEnviados($resource, $perfisExistentes, $perfis);

                // // Se for enviado dados de usuário
                // if ($user) {
                //     if ($user->id) {
                //         $user->save();
                //     } else {
                //         $perfilUsuario = $this->modelPessoaPerfil::where('pessoa_id', $resource->pessoa->id)->where('perfil_tipo_id', PessoaPerfilTipoEnum::USUARIO->value)->first();

                //         $user->pessoa_perfil_id = $perfilUsuario->id;
                //         $user->tenant_id = tenant('id');
                //         $user->save();
                //     }

                //     if ($userDomains || $userDomainsExistentes) {
                //         // Fazer salvamento dos domínios
                //         $this->atualizarUserDomainsEnviados($resource, $userDomainsExistentes, $userDomains, $user);
                //     }
                // }

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

        // Verifica e processa documentos
        $documentosProcessados = $this->verificacaoDocumentos($requestData, $resource, $arrayErrors);
        $resource->documentos = $documentosProcessados->documentos;
        $arrayErrors = $documentosProcessados->arrayErrors;

        // Verifica e processa perfis
        $perfisProcessados = $this->verificacaoPerfis($requestData, $resource, $arrayErrors);
        $resource->perfis = $perfisProcessados->perfis;
        $arrayErrors = $perfisProcessados->arrayErrors;

        // // Verificação específica para tipo de perfil USUARIO
        // if ($requestData->pessoa_perfil_tipo_id === PessoaPerfilTipoEnum::USUARIO->value) {

        //     // Verifica e processa domínios
        //     $userDomainsProcessados = $this->verificacaoUserDomains($requestData, $resource, $arrayErrors);
        //     $resource->userDomains = $userDomainsProcessados->userDomains;
        //     $arrayErrors = $userDomainsProcessados->arrayErrors;

        //     // Verifica e processa o usuário
        //     $userProcessado = $this->verificarUsuario($requestData, $resource, $arrayErrors);
        //     $resource->user = $userProcessado->user;
        //     $arrayErrors = $userProcessado->arrayErrors;
        // }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        // Preenche os dados do recurso
        $resource->fill($requestData->toArray());

        return $resource;
    }

    // public function store(Fluent $requestData)
    // {
    //     $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

    //     try {
    //         return DB::transaction(function () use ($resource, $requestData) {

    //             $documentos = $resource->documentos;
    //             unset($resource->documentos);

    //             //Salva os dados da Pessoa Jurídica
    //             $resource->save();

    //             // Salva os dados da Pessoa
    //             $pessoa = new $this->modelPessoa;
    //             $pessoa->pessoa_dados_type = $this->model->getMorphClass();
    //             $pessoa->pessoa_dados_id = $resource->id;
    //             $pessoa->save();

    //             // Salva os dados do perfil
    //             $pessoaPerfil = new $this->modelPessoaPerfil;
    //             $pessoaPerfil->pessoa_id = $pessoa->id;
    //             $pessoaPerfil->perfil_tipo_id = $requestData->pessoa_perfil_tipo_id;
    //             $pessoaPerfil->save();

    //             // Fazer salvamento dos documentos
    //             foreach ($documentos as $documento) {
    //                 $documento->pessoa_id = $pessoa->id;
    //                 $documento->save();
    //             }

    //             // $this->executarEventoWebsocket();
    //             return $resource->toArray();
    //         });
    //     } catch (\Exception $e) {
    //         return $this->gerarLogExceptionErroSalvar($e);
    //     }
    // }

    // public function update(Fluent $requestData)
    // {
    //     $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

    //     try {
    //         return DB::transaction(function () use ($resource) {

    //             // Obter e remover do resource os documentos
    //             $documentos = $resource->documentos;
    //             unset($resource->documentos);

    //             // Busca os documentos existentes
    //             $documentosExistentes = $resource->pessoa->documentos;

    //             $resource->save();

    //             // Fazer salvamento dos documentos
    //             $this->atualizarDocumentosEnviados($resource, $documentosExistentes, $documentos);

    //             // $this->executarEventoWebsocket();
    //             return $resource->toArray();
    //         });
    //     } catch (\Exception $e) {
    //         return $this->gerarLogExceptionErroSalvar($e);
    //     }
    // }

    // protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    // {
    //     $arrayErrors = new Fluent();

    //     $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

    //     $documentosProcessados = $this->verificacaoDocumentos($requestData, $resource);
    //     $resource->documentos = $documentosProcessados->documentos;
    //     $arrayErrors = new Fluent(array_merge($arrayErrors->toArray(), $documentosProcessados->arrayErrors->toArray()));

    //     // Erros que impedem o processamento
    //     CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

    //     $resource->fill($requestData->toArray());

    //     return $resource;
    // }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Pessoa Jurídica não foi encontrada.',
        ], $options));
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [];

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
