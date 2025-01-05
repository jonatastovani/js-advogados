<?php

namespace App\Http\Controllers\View\Financeiro;

use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PdfMarginPresetsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\MovimentacaoContaParticipante\PostConsultaFiltroFormRequestBalancoRepasseParceiro;
use App\Http\Requests\Financeiro\MovimentacaoConta\PostConsultaFiltroFormRequestMovimentacaoConta;
use App\Services\Financeiro\MovimentacaoContaParticipanteService;
use App\Services\Financeiro\MovimentacaoContaService;
use App\Services\Pdf\PdfGenerator;
use App\Traits\CommonsControllerMethodsTrait;
use App\Utils\CurrencyFormatterUtils;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class FinanceiroController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(
        public MovimentacaoContaService $service,
        public MovimentacaoContaParticipanteService $serviceMovimentacaoContaParticipante
    ) {}

    public function financeiroIndex()
    {
        return view('secao.financeiro.index');
    }

    public function lancamentosServicosIndex()
    {
        return view('secao.financeiro.lancamentos-servicos.index');
    }

    public function lancamentosGeraisIndex()
    {
        return view('secao.financeiro.lancamentos-gerais.index');
    }

    public function lancamentosAgendamentosIndex()
    {
        return view('secao.financeiro.lancamentos-agendamentos.index');
    }

    public function movimentacaoContaIndex()
    {
        return view('secao.financeiro.movimentacao-conta.index');
    }

    public function movimentacaoContaImpressao(PostConsultaFiltroFormRequestMovimentacaoConta $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        $dados = $this->service->postConsultaFiltros($fluentData, ['withOutPagination' => true]);

        $dataEnv = new Fluent([
            'dados' => $dados,
            'margins' => PdfMarginPresetsEnum::ESTREITA->detalhes(),
            'mes_ano' => Carbon::parse($fluentData->mes_ano)->translatedFormat('F/Y'),
            // 'data_inicio' => (new DateTime($fluentData->datas_intervalo['data_inicio']))->format('d/m/Y'),
            // 'data_fim' => (new DateTime($fluentData->datas_intervalo['data_fim']))->format('d/m/Y'),
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

        // Cria a chave `processedData` no objeto Fluent
        $dataEnv->processedData = $processedData;

        return $dataEnv;
    }

    public function balancoRepasseParceiroIndex()
    {
        return view('secao.financeiro.balanco-repasse-parceiro.index');
    }

    public function balancoRepasseParceiroImpressao(PostConsultaFiltroFormRequestBalancoRepasseParceiro $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        $dados = $this->serviceMovimentacaoContaParticipante->postConsultaFiltrosBalancoRepasseParceiro($fluentData, ['withOutPagination' => true]);

        // dd($dados);
        $dataEnv = new Fluent([
            'dados' => $dados,
            'margins' => PdfMarginPresetsEnum::ESTREITA->detalhes(),
            'mes_ano' => Carbon::parse($fluentData->mes_ano)->translatedFormat('F/Y'),
        ]);

        $dataEnv = $this->balancoRepasseParceiroImpressaoRenderInfo($dataEnv);

        // Configurações personalizadas de PDF
        $pdfService = new PdfGenerator([
            'orientation' => 'landscape',
            'paper' => 'A4',
        ]);

        return $pdfService->generate('secao.financeiro.balanco-repasse-parceiro.impressao', compact('dataEnv'));
    }

    private function balancoRepasseParceiroImpressaoRenderInfo(Fluent $dataEnv)
    {
        $processedData = [];

        foreach ($dataEnv->dados as $value) {
            $dadosRetorno = new Fluent();
            $parent = $value['parent'];

            $dadosRetorno->status = $parent['status']['nome'];
            $dadosRetorno->movimentacao_tipo = $parent['movimentacao_tipo']['nome'];
            $dadosRetorno->valor_participante = CurrencyFormatterUtils::toBRL($value['valor_participante']);
            $dadosRetorno->data_movimentacao = (new DateTime($parent['data_movimentacao']))->format('d/m/Y');
            $dadosRetorno->descricao_automatica = $parent['descricao_automatica'];

            $dadosEspecificos = $value['parent']['descricao_automatica'];

            switch ($value['parent']['referencia_type']) {
                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    $dadosEspecificos .= " - Serviço {$parent['referencia']['pagamento']['servico']['numero_servico']}";
                    $dadosEspecificos .= " - Pagamento - {$parent['referencia']['pagamento']['numero_pagamento']}";
                    $dadosEspecificos .= " - {$parent['referencia']['pagamento']['servico']['area_juridica']['nome']}";
                    $dadosEspecificos .= " - {$parent['referencia']['pagamento']['servico']['titulo']}";
                    break;

                default:
                    break;
            }
            $dadosRetorno->dados_especificos = $dadosEspecificos;
            $dadosRetorno->conta = $parent['conta']['nome'];

            $dadosRetorno->created_at = (new DateTime($parent['created_at']))->format('d/m/Y H:i:s');

            $processedData[] = $dadosRetorno->toArray();
        }

        // Cria a chave `processedData` no objeto Fluent
        $dataEnv->processedData = $processedData;
        $dataEnv->total_credito = collect($dataEnv->dados)->where('parent.movimentacao_tipo_id', MovimentacaoContaTipoEnum::CREDITO->value)->sum('valor_participante');
        $dataEnv->total_debito = collect($dataEnv->dados)->where('parent.movimentacao_tipo_id', MovimentacaoContaTipoEnum::DEBITO->value)->sum('valor_participante');
        $dataEnv->total_saldo = CurrencyFormatterUtils::toBRL(bcsub($dataEnv->total_credito, $dataEnv->total_debito, 2));
        $dataEnv->total_credito = CurrencyFormatterUtils::toBRL($dataEnv->total_credito);
        $dataEnv->total_debito = CurrencyFormatterUtils::toBRL($dataEnv->total_debito);

        $first = $dataEnv->dados[0];
        
        $dataEnv->dados_participante = $dataEnv->dados[0];
        
        return $dataEnv;
    }

    public function painelContasIndex()
    {
        return view('secao.financeiro.painel-contas.index');
    }
}
