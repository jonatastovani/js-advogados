<?php

namespace App\Services\Referencias;

use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Models\Referencias\MovimentacaoContaStatusTipo;
use App\Services\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

class MovimentacaoContaStatusTipoService extends Service
{
    public function __construct(public MovimentacaoContaStatusTipo $model) {}

    public function index(Fluent $requestData)
    {
        $idsOcultos = MovimentacaoContaStatusTipoEnum::statusOcultoNasConsultas();

        $resource = $this->model
            ->whereNotIn('id', $idsOcultos) // Exclui os registros com os IDs ocultos
            ->orderBy('nome', 'asc')
            ->get()
            ->toArray();

        return $resource;
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
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O tipo de movimentação não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}