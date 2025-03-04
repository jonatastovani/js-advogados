<?php

namespace App\Services\Tenant;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PessoaTipoEnum;
use App\Helpers\DocumentoModeloQuillEditorHelper;
use App\Helpers\DocumentoModeloTenantRenderizarHelper;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\DocumentoModeloTipo;
use App\Models\Tenant\DocumentoModeloTenant;
use App\Services\Pessoa\PessoaPerfilService;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class DocumentoModeloTenantService extends Service
{

    public function __construct(
        DocumentoModeloTenant $model,
        public DocumentoModeloTipo $modelDocumentoModeloTipo
    ) {
        parent::__construct($model);
    }

    public function indexPorDocumentoModeloTipo(Fluent $requestData)
    {
        $resource = $this->model->select('id', 'nome')->where("documento_modelo_tipo_id", $requestData->documento_modelo_tipo_id)
            ->orderBy("nome", 'asc')->get();
        return $resource->toArray();
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
        $arrayErrors = new Fluent();

        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este modelo de documento já existe.', $requestData->toArray());

            RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $validacaoConteudoEObjetos = $this->verificacaoDocumentoEmCriacao($requestData);
        if ((isset($validacaoConteudoEObjetos['marcacoes_sem_referencia']) && count($validacaoConteudoEObjetos['marcacoes_sem_referencia']) > 0)
            || (isset($validacaoConteudoEObjetos['objetos_nao_utilizados']) && count($validacaoConteudoEObjetos['objetos_nao_utilizados']) > 0)
        ) {
            $arrayErrors->conteudo =  LogHelper::gerarLogDinamico(409, 'Há marcações sem referência ou objetos não utilizados no modelo.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        /** @var DocumentoModeloTenant  */
        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;
        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O Modelo de Documento não foi encontrado.',
        ]);
    }

    public function verificacaoDocumentoEmCriacao(Fluent $requestData, array $options = [])
    {
        return DocumentoModeloQuillEditorHelper::verificarInconsistencias($requestData, $options);
    }

    public function renderObjetos(Fluent $requestData, array $options = [])
    {
        // Objetos a serem retornados
        $objetosRetorno = [];

        // Agrupa os objetos por identificador para fazer os carregamentos
        $agrupadoPorIdentificador = collect($requestData->objetos)->groupBy('identificador');

        $agrupadoPorIdentificador->each(function ($objetosPorIdentificador, $identificador) use (&$objetosRetorno) {

            switch ($identificador) {

                case 'ClientePF':
                    // ClientePF busca pelo PessoaPerfil
                    $perfis = PessoaPerfil::with(app(PessoaPerfilService::class)->loadFull(['caseTipoPessoa' => PessoaTipoEnum::PESSOA_FISICA->value]))
                        ->whereIn('id', $objetosPorIdentificador->pluck('id')->toArray())->get();

                    $objetosRetorno = array_merge($objetosRetorno, $this->preparaObjetosClientesPFPJ($perfis->toArray(), $objetosPorIdentificador));
                    break;

                case 'ClientePJ':
                    // ClientePF busca pelo PessoaPerfil
                    $perfis = PessoaPerfil::with(app(PessoaPerfilService::class)->loadFull(['caseTipoPessoa' => PessoaTipoEnum::PESSOA_JURIDICA->value]))
                        ->whereIn('id', $objetosPorIdentificador->pluck('id')->toArray())->get();

                    $objetosRetorno = array_merge($objetosRetorno, $this->preparaObjetosClientesPFPJ($perfis->toArray(), $objetosPorIdentificador));
                    break;
            }
        });

        return $objetosRetorno;
    }

    private function preparaObjetosClientesPFPJ(array $perfis, Collection $objetosPorIdentificador): array
    {
        $objetosRetorno = [];

        collect($perfis)->each(function ($perfil) use (&$objetosRetorno, $objetosPorIdentificador) {

            $objetoEnviado = $objetosPorIdentificador->where('id', $perfil['id'])->first();


            $pessoaDados = $perfil['pessoa']['pessoa_dados'];
            $pessoaDados['documento'] = $perfil['pessoa']['documentos'] ?? [];
            $pessoaDados['endereco'] = $perfil['pessoa']['enderecos'] ?? [];
            $objetosRetorno[] = array_merge($objetoEnviado, [
                'dados' => $pessoaDados
            ]);
        });

        return $objetosRetorno;
    }

    public function verificacaoDocumentoRenderizar(Fluent $requestData, array $options = [])
    {
        $requestData->objetos_vinculados = $this->renderObjetos(new Fluent([
            'objetos' => $requestData->objetos_vinculados,
        ]));

        return DocumentoModeloTenantRenderizarHelper::verificarInconsistencias($requestData);
    }

    public function loadFull($options = []): array
    {
        return ['documento_modelo_tipo'];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
