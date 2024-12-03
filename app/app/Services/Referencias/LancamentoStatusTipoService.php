<?php

namespace App\Services\Referencias;

use App\Enums\LancamentoStatusTipoEnum;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class LancamentoStatusTipoService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(LancamentoStatusTipo $model)
    {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    // public function select2(Request $request)
    // {
    //     $dados = new Fluent([
    //         'camposFiltros' => ['nome', 'descricao'],
    //     ]);

    //     return $this->executaConsultaSelect2($request, $dados);
    // }

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

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        // $resource = new Fluent($resource->toArray());

        $html = '';
        switch ($resource->id) {
            case LancamentoStatusTipoEnum::LIQUIDADO->value:
                $html = view('components.modal.financeiro.modal-lancamento-servico-movimentar.campos-personalizados.liquidado', compact('requestData'))->render();
                break;

            case LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value:
                $html = view('components.modal.financeiro.modal-lancamento-servico-movimentar.campos-personalizados.liquidado-parcialmente', compact('requestData'))->render();
                break;

            case LancamentoStatusTipoEnum::REAGENDADO->value:
                $html = view('components.modal.financeiro.modal-lancamento-servico-movimentar.campos-personalizados.reagendado', compact('requestData'))->render();
                break;
        }

        $resource->campos_html = $html;

        return $resource->toArray();
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O status de lancamento não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
