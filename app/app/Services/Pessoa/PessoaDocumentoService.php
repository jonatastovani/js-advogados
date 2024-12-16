<?php

namespace App\Services\Pessoa;

use App\Enums\DocumentoTipoEnum;
use App\Enums\PessoaDocumentoTipoEnum;
use App\Models\Servico\Servico;
use App\Models\Pessoa\PessoaDocumento;
use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class PessoaDocumentoService extends Service
{
    public function __construct(PessoaDocumento $model)
    {
        parent::__construct($model);
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_numero'] (mapeado para '[tableAsName].numero')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $permissionAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_numero' => isset($aliasCampos['col_numero']) ? $aliasCampos['col_numero'] : $permissionAsName,
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_numero' => ['campo' => $arrayAliasCampos['col_numero'] . '.numero'],
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_numero'], $dados);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->all();
        return $resource->toArray();
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O documento não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
