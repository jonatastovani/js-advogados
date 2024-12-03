<?php

namespace App\Services\Financeiro;

use App\Models\Financeiro\LancamentoGeral;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Fluent;

class LancamentoGeralService extends Service
{

    public function __construct(LancamentoGeral $model)
    {
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
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();

        $arrayAliasCampos = [

            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao_automatica'],
        ];

        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $filtrosData['query'];
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => 'data_vencimento', 'direcao' => 'asc']]
            );
        }

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_vencimento',
        ], $options));

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Lançamento não foi encontrado.',
            'conditions' => [
                'id' => $requestData->uuid,
                'pagamento_id' => $requestData->pagamento_uuid
            ]
        ], $options));
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
