<?php

namespace App\Services\Tenant;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\ContaTenant;
use App\Models\Tenant\FormaPagamentoTenant;
use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class FormaPagamentoTenantService extends Service
{

    public function __construct(FormaPagamentoTenant $model)
    {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->where('ativo_bln', true)->orderBy('nome', 'asc')->get();
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
            'col_banco' => isset($aliasCampos['col_banco']) ? $aliasCampos['col_banco'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
            'col_banco' => ['campo' => $arrayAliasCampos['col_banco'] . '.banco'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta forma de pagamento já existe.', $requestData->toArray());
            RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $arrayErrors = new Fluent();

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model();

        $validacaoContaTenantId = ValidationRecordsHelper::validateRecord(ContaTenant::class, ['id' => $requestData->conta_id]);
        if (!$validacaoContaTenantId->count()) {
            $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Forma de Pagamento não foi encontrada.',
        ]);
    }

    public function validacaoRecurso(Fluent $requestData, Fluent $arrayErrors, array $options = []): Fluent
    {
        $nomePropriedade = $options['nome_propriedade_forma_pagamento'] ?? 'forma_pagamento_id';
        $validacaoConta = $options['validacao_conta'] ?? true;

        $validacaoFormaPagamento = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $requestData->$nomePropriedade]);

        if (!$validacaoFormaPagamento->count()) {
            $arrayErrors->$nomePropriedade = LogHelper::gerarLogDinamico(404, 'A Forma de Pagamento informada não existe ou foi excluída.', $requestData)->error;
        } else {
            if ($validacaoFormaPagamento->first()->ativo_bln != true) {
                $arrayErrors->$nomePropriedade = LogHelper::gerarLogDinamico(404, 'A Forma de Pagamento encontra-se inativa. Verifique o motivo!.', $requestData)->error;
            } else {
                if ($validacaoConta) {
                    // Verificar se a conta está com status que permite movimentação
                    $requestData->conta_id = $validacaoFormaPagamento->first()->conta_id;
                    $validacaoContaTenant = app(ContaTenantService::class)->validacaoRecurso($requestData, $arrayErrors);
                    $arrayErrors = $validacaoContaTenant->arrayErrors;
                }
            }
        }
        return new Fluent([
            'arrayErrors' => $arrayErrors,
            'resource' => $validacaoFormaPagamento,
        ]);
    }

    public function loadFull($options = []): array
    {
        return [
            'conta',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
