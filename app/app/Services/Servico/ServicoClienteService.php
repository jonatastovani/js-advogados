<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoCliente;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoClienteService extends Service
{

    public function __construct(
        ServicoCliente $model,
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

        return false;
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();

        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model::with($this->loadFull())
            ->where('servico_id', $requestData->servico_uuid)->get();
        return $resource->toArray();
    }

    public function store(Fluent $requestData)
    {
        $resources = $this->verificacaoEPreenchimentoRecursoStore($requestData);

        try {
            return DB::transaction(function () use ($resources, $requestData) {

                $arrayRetorno = [];
                $clientes = $resources->clientes;

                // IDs dos clientes já salvos
                $existingClientes = $this->model::where('servico_id', $requestData->servico_uuid)
                    ->pluck('id')->toArray();
                // IDs enviados (exclui novos clientes sem ID)
                $submittedClienteIds = collect($clientes)->pluck('id')->filter()->toArray();

                // Clientes ausentes no PUT devem ser excluídos
                $idsToDelete = array_diff($existingClientes, $submittedClienteIds);
                if ($idsToDelete) {
                    foreach ($idsToDelete as $id) {
                        $clienteDelete = $this->model::find($id);
                        if ($clienteDelete) {
                            $clienteDelete->delete();
                        }
                    }
                }

                foreach ($clientes as $cliente) {

                    $salvarELoad = function ($clienteSave) use (&$arrayRetorno) {

                        $clienteSave->save();
                        $clienteSave->load($this->loadFull());
                        $arrayRetorno[] = $clienteSave->toArray();
                    };

                    if ($cliente->id) {
                        $clienteUpdate = $this->model::find($cliente->id);
                        $clienteUpdate->fill($cliente->toArray());
                        $salvarELoad($clienteUpdate);
                    } else {
                        $cliente->servico_id = $requestData->servico_uuid;
                        $salvarELoad($cliente);
                    }
                }

                return $arrayRetorno;
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStore(Fluent $requestData): Fluent
    {
        $arrayErrors = new Fluent();

        //Verifica se o serviço informado existe
        $validacaoServicoId = ValidationRecordsHelper::validateRecord(Servico::class, ['id' => $requestData->servico_uuid]);
        if (!$validacaoServicoId->count()) {
            $error = LogHelper::gerarLogDinamico(404, 'O Serviço informado não existe ou foi excluído.', $requestData)->error;
            RestResponse::createErrorResponse($error->code, $error->error, $error->trace_id)->throwResponse();
        }

        $clientesData = $this->verificacaoCliente($requestData->clientes, $arrayErrors);
        CommonsFunctions::retornaErroQueImpedemProcessamento422($clientesData->arrayErrors->toArray());

        return new Fluent([
            'clientes' => $clientesData->clientes,
        ]);
    }

    protected function verificacaoCliente(array $clientesData, Fluent $arrayErrors): Fluent
    {
        $clientes = [];
        foreach ($clientesData as $cliente) {
            $cliente = new Fluent($cliente);

            //Verifica se o tipo de registro de participação informado existe
            $validacaoPerfilClienteId = ValidationRecordsHelper::validateRecord(PessoaPerfil::class, ['id' => $cliente->perfil_id]);
            if (!$validacaoPerfilClienteId->count()) {
                $arrayErrors["perfil_id_{$cliente->perfil_id}"] = LogHelper::gerarLogDinamico(404, 'O Perfil informado não existe ou foi excluído.', $clientesData)->error;
            } else {

                // Se existir, verificamos se o perfil é um perfil permitido para ser incluso no serviço
                if (!$validacaoPerfilClienteId->whereIn('perfil_tipo_id', PessoaPerfilTipoEnum::perfisPermitidoClienteServico())) {
                    $pessoa = $validacaoPerfilClienteId->first()->pessoa;
                    $nomePessoa = 'Não encontrada';
                    switch ($pessoa->pessoa_tipo_id) {
                        case PessoaTipoEnum::PESSOA_FISICA->value:
                            $nomePessoa = $pessoa->pessoa_dados->nome;
                            break;

                        case PessoaTipoEnum::PESSOA_JURIDICA->value:
                            $nomePessoa = $pessoa->pessoa_dados->nome_fantasia;
                            break;

                        default:
                            $error = LogHelper::gerarLogDinamico(404, 'O Tipo de pessoa não existe ou não foi configurado.', $clientesData)->error;
                            return RestResponse::createErrorResponse($error->code, $error->error, $error->trace_id)->throwResponse();
                    }

                    $arrayErrors["perfil_id_{$cliente->perfil_id}"] = LogHelper::gerarLogDinamico(404, "O Perfil da pessoa $nomePessoa não é permitido para ser inserido como cliente. Verifique os perfis permitidos.", $clientesData)->error;
                }
            }

            array_push($clientes, (new $this->model())->fill($cliente->toArray()));
        }

        return new Fluent([
            'clientes' => $clientes,
            'arrayErrors' => $arrayErrors,
        ]);
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            'perfil.perfil_tipo',
            'perfil.pessoa.pessoa_dados',
            'perfil.pessoa.enderecos',
        ];

        // Verifica se ServicoService está na lista de exclusão
        $classImport = ServicoService::class;
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
                    'addPrefix' => 'servico.' // Adiciona um prefixo aos relacionamentos externos
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
