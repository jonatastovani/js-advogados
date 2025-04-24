<?php

namespace App\Http\Controllers\View\Financeiro;

use App\Enums\BalancoRepasseTipoParentEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PdfMarginPresetsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\MovimentacaoContaParticipante\PostConsultaFiltroFormRequestBalancoRepasse;
use App\Http\Requests\Financeiro\MovimentacaoConta\PostConsultaFiltroFormRequestMovimentacaoConta;
use App\Services\Financeiro\MovimentacaoContaParticipanteService;
use App\Services\Financeiro\MovimentacaoContaService;
use App\Services\Pdf\PdfGenerator;
use App\Traits\CommonsControllerMethodsTrait;
use App\Utils\CurrencyFormatterUtils;
use Carbon\Carbon;
use DateTime;
use Exception;
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

    public function lancamentosAgendamentosIndex()
    {
        return view('secao.financeiro.lancamentos-agendamentos.index');
    }

    public function balancoRepasseIndex()
    {
        return view('secao.financeiro.balanco-repasse.index');
    }

    public function balancoRepasseImpressao(PostConsultaFiltroFormRequestBalancoRepasse $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        $dados = $this->serviceMovimentacaoContaParticipante->postConsultaFiltrosBalancoRepasse($fluentData, ['withOutPagination' => true]);

        $somatorias = $this->serviceMovimentacaoContaParticipante->obterTotaisParticipacoes(collect($dados));

        // dd($dados);
        $dataEnv = new Fluent([
            'dados' => $dados,
            'somatorias' => $somatorias,
            'margins' => PdfMarginPresetsEnum::ESTREITA->detalhes(),
            'mes_ano' => Carbon::parse($fluentData->mes_ano)->translatedFormat('F/Y'),
        ]);

        $dataEnv = $this->balancoRepasseImpressaoRenderInfo($dataEnv);

        // Configurações personalizadas de PDF
        $pdfService = new PdfGenerator([
            'orientation' => 'landscape',
            'paper' => 'A4',
        ]);

        return $pdfService->generate('secao.financeiro.balanco-repasse.impressao', compact('dataEnv'));
    }

    private function balancoRepasseImpressaoRenderInfo(Fluent $dataEnv)
    {
        $processedData = [];

        foreach ($dataEnv->dados as $participacao) {
            $dadosRetorno = new Fluent();

            $parent = $participacao['parent'];
            $dadosRetorno->status = $participacao['status']['nome'];
            $dadosRetorno->valor_participante = CurrencyFormatterUtils::toBRL($participacao['valor_participante']);
            $dadosRetorno->descricao_automatica = $participacao['descricao_automatica'];

            switch ($participacao['parent_type']) {

                case BalancoRepasseTipoParentEnum::MOVIMENTACAO_CONTA->value:

                    $dadosRetorno->data_movimentacao = (new DateTime($parent['data_movimentacao']))->format('d/m/Y');
                    $dadosRetorno->movimentacao_tipo = $parent['movimentacao_tipo']['nome'];
                    $dadosRetorno->conta = $parent['conta_domain']['conta']['nome'];

                    $dadosEspecificos = $parent['descricao_automatica'];

                    switch ($participacao['parent']['referencia_type']) {

                        case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                            // $dadosEspecificos .= " - Serviço {$parent['referencia']['pagamento']['servico']['numero_servico']}";
                            $dadosEspecificos .= " - NP#{$parent['referencia']['pagamento']['numero_pagamento']}";
                            $dadosEspecificos .= " - ({$parent['referencia']['pagamento']['servico']['area_juridica']['nome']})";
                            $dadosEspecificos .= " - {$parent['referencia']['pagamento']['servico']['titulo']}";
                            break;

                        // case MovimentacaoContaReferenciaEnum::LANCAMENTO_GERAL->value:

                        //     $dadosEspecificos .= " - NL#{$parent['referencia']['numero_lancamento']}";
                        //     $dadosEspecificos .= " - ({$parent['referencia']['categoria']['nome']})";
                        // break;

                        default:
                            throw new Exception('Tipo de referência de movimentação de conta não configurado.', 500);
                            break;
                    }
                    break;

                case BalancoRepasseTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value:

                    $dadosRetorno->data_movimentacao = (new DateTime($parent['data_vencimento']))->format('d/m/Y');
                    $dadosRetorno->movimentacao_tipo = $parent['parceiro_movimentacao_tipo']['nome'];
                    $dadosRetorno->conta = $parent['conta']['nome'];

                    $dadosEspecificos = $participacao['descricao_automatica'];
                    $dadosEspecificos .= " - NR#{$parent['numero_lancamento']}";
                    $dadosEspecificos .= " - ({$parent['categoria']['nome']})";
                    $dadosEspecificos .= " - {$parent['descricao']}";
                    break;

                default:
                    throw new Exception('Tipo parent de registro de balanço de parceiro não configurado.', 500);
                    break;
            }

            $dadosRetorno->dados_especificos = $dadosEspecificos;
            $dadosRetorno->created_at = (new DateTime($parent['created_at']))->format('d/m/Y H:i:s');

            $processedData[] = $dadosRetorno->toArray();
        }

        // Cria a chave `processedData` no objeto Fluent
        $dataEnv->processedData = $processedData;
        $dataEnv->dados_participante = $dataEnv->dados[0];
        $dataEnv->somatorias = CurrencyFormatterUtils::convertArrayToBRL($dataEnv->somatorias->toArray());

        return $dataEnv;
    }

    public function lancamentosGeraisIndex()
    {
        return view('secao.financeiro.lancamentos-gerais.index');
    }

    public function lancamentosRessarcimentosIndex()
    {
        return view('secao.financeiro.lancamentos-ressarcimentos.index');
    }

    public function lancamentosServicosIndex()
    {
        return view('secao.financeiro.lancamentos-servicos.index');
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
            $dadosRetorno->conta = $value['conta_domain']['conta']['nome'];
            $dadosRetorno->descricao_automatica = $value['descricao_automatica'];
            $dadosRetorno->observacao = $value['observacao'];

            $dadosEspecificos = '';

            switch ($value['referencia_type']) {

                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    // $dadosEspecificos = "NS#{$value['referencia']['pagamento']['servico']['numero_servico']}";
                    $dadosEspecificos .= "NP#{$value['referencia']['pagamento']['numero_pagamento']}";
                    $dadosEspecificos .= " - ({$value['referencia']['pagamento']['servico']['area_juridica']['nome']})";
                    $dadosEspecificos .= " - {$value['referencia']['pagamento']['servico']['titulo']}";
                    break;

                case MovimentacaoContaReferenciaEnum::LANCAMENTO_GERAL->value:
                    $dadosEspecificos = "NL#{$value['referencia']['numero_lancamento']}";
                    $dadosEspecificos .= " - ({$value['referencia']['categoria']['nome']})";
                    $dadosEspecificos .= " - {$value['descricao_automatica']}";
                    break;

                case MovimentacaoContaReferenciaEnum::DOCUMENTO_GERADO->value:
                    $dadosEspecificos = "ND#{$value['referencia']['numero_documento']}";
                    $dadosEspecificos .= " - {$value['descricao_automatica']}";
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

    public function painelContasIndex()
    {
        return view('secao.financeiro.painel-contas.index');
    }

    public function pagamentosServicosIndex()
    {
        return view('secao.financeiro.pagamentos-servicos.index');
    }
}
