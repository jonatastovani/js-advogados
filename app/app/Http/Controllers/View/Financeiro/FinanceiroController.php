<?php

namespace App\Http\Controllers\View\Financeiro;

use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\PdfMarginPresetsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use App\Models\Financeiro\MovimentacaoConta;
use App\Services\Financeiro\MovimentacaoContaService;
use App\Services\Pdf\PdfGenerator;
use App\Traits\CommonsControllerMethodsTrait;
use App\Utils\CurrencyFormatterUtils;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class FinanceiroController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public MovimentacaoContaService $service) {}

    public function financeiroIndex()
    {
        return view('secao.financeiro.index');
    }

    public function lancamentosServicosIndex()
    {
        return view('secao.financeiro.lancamentos-servicos.index');
    }

    public function movimentacaoContaIndex()
    {
        return view('secao.financeiro.movimentacao-conta.index');
    }

    public function movimentacaoContaImpressao(PostConsultaFiltroFormRequestBase $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        $dados = $this->service->postConsultaFiltros($fluentData, ['withOutPagination' => true]);

        $dataEnv = new Fluent([
            'dados' => $dados,
            'margins' => PdfMarginPresetsEnum::ESTREITA->detalhes(),
            'data_inicio' => (new DateTime($fluentData->datas_intervalo['data_inicio']))->format('d/m/Y'),
            'data_fim' => (new DateTime($fluentData->datas_intervalo['data_fim']))->format('d/m/Y'),
        ]);

        $dataEnv = $this->movimentacaoContaImpressaoRenderInfo($dataEnv);

        // Configurações personalizadas de PDF
        $pdfService = new PdfGenerator([
            'orientation' => 'landscape',
            'paper' => 'A4',
        ]);

        return $pdfService->generate('secao.financeiro.movimentacao-conta.impressao', compact('dataEnv'));
    }

    private function movimentacaoContaImpressaoRenderInfo(Fluent $dataEnv)
    {
        $processedData = [];

        foreach ($dataEnv->dados as $value) {
            $dadosRetorno = new Fluent();

            $dadosRetorno->status = $value['status']['nome'];
            $dadosRetorno->movimentacao_tipo = $value['movimentacao_tipo']['nome'];
            $dadosRetorno->valor_movimentado = CurrencyFormatterUtils::toBRL($value['valor_movimentado']);
            $dadosRetorno->data_movimentacao = (new DateTime($value['data_movimentacao']))->format('d/m/Y');
            $dadosRetorno->conta = $value['conta']['nome'];
            $dadosRetorno->descricao_automatica = $value['descricao_automatica'];
            $dadosRetorno->observacao = $value['observacao'];

            $dadosEspecificos = '';

            switch ($value['referencia_type']) {
                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    $dadosEspecificos = "Serviço {$value['referencia']['pagamento']['servico']['numero_servico']}";
                    $dadosEspecificos .= " - Pagamento - {$value['referencia']['pagamento']['numero_pagamento']}";
                    $dadosEspecificos .= " - {$value['referencia']['pagamento']['servico']['area_juridica']['nome']}";
                    $dadosEspecificos .= " - {$value['referencia']['pagamento']['servico']['titulo']}";
                    break;

                default:
                    break;
            }
            $dadosRetorno->dados_especificos = $dadosEspecificos;

            $dadosRetorno->created_at = (new DateTime($value['created_at']))->format('d/m/Y H:i:s');

            $processedData[] = $dadosRetorno->toArray();
        }

        // Atualizar `dados` no objeto Fluent
        $dataEnv->dados = $processedData;

        return $dataEnv;
    }

    public function balancoRepasseParceiroIndex()
    {
        return view('secao.financeiro.balanco-repasse-parceiro.index');
    }
}
