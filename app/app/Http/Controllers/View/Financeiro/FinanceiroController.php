<?php

namespace App\Http\Controllers\View\Financeiro;

use App\Enums\BalancoRepasseTipoParentEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PdfMarginPresetsEnum;
use App\Helpers\PessoaNomeHelper;
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

            $dadosEspecificos = [];

            switch ($participacao['parent_type']) {

                case BalancoRepasseTipoParentEnum::MOVIMENTACAO_CONTA->value:

                    $dadosRetorno->data_movimentacao = (new DateTime($parent['data_movimentacao']))->format('d/m/Y');
                    $dadosRetorno->movimentacao_tipo = $parent['movimentacao_tipo']['nome'];
                    $dadosRetorno->conta = $parent['conta_domain']['conta']['nome'];
                    $referencia = $parent['referencia'];
                    $pagamento = $referencia['pagamento'];
                    $servico = $pagamento['servico'];

                    switch ($participacao['parent']['referencia_type']) {

                        case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:

                            $dadosEspecificos[] = $this->htmlRenderCliente($referencia);
                            $dadosEspecificos[] = $servico['titulo'];
                            $dadosEspecificos[] = "({$servico['area_juridica']['nome']})";
                            $dadosEspecificos[] = $parent['descricao_automatica'];
                            $dadosEspecificos[] = "NP#{$pagamento['numero_pagamento']}";
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

                    $dadosEspecificos[] = $participacao['descricao_automatica'];
                    $dadosEspecificos[] = "NR#{$parent['numero_lancamento']}";
                    $dadosEspecificos[] = "({$parent['categoria']['nome']})";
                    $dadosEspecificos[] = "{$parent['descricao']}";
                    break;

                default:
                    throw new Exception('Tipo parent de registro de balanço de parceiro não configurado.', 500);
                    break;
            }

            $dadosRetorno->dados_especificos = implode(' - ', $dadosEspecificos);
            $dadosRetorno->created_at = (new DateTime($parent['created_at']))->format('d/m/Y H:i:s');

            $processedData[] = $dadosRetorno->toArray();
        }

        // Cria a chave `processedData` no objeto Fluent
        $dataEnv->processedData = $processedData;

        $dataEnv->participante_nome = PessoaNomeHelper::extrairNome($dataEnv->dados[0]['referencia'])['nome_completo'];
        $dataEnv->participante_perfil_nome = $dataEnv->dados[0]['referencia']['perfil_tipo']['nome'];

        $dataEnv->somatorias = CurrencyFormatterUtils::convertArrayToBRL($dataEnv->somatorias->toArray());

        return $dataEnv;
    }

    /**
     * Renderiza o nome do cliente a partir de um lançamento de serviço.
     * Se houver mais de um cliente, mostra o primeiro nome seguido de "+ N".
     *
     * @param array $lancamentoServico Array contendo as relações de pagamento > servico > cliente.
     * @return string Nome renderizado ou mensagem padrão.
     */
    private function htmlRenderCliente(array $lancamentoServico): string
    {
        $clientes = $lancamentoServico['pagamento']['servico']['cliente'] ?? [];

        if (empty($clientes)) {
            return '';
        }

        // Extrai nomes usando a PessoaNomeHelper (espera array com chave 'perfil')
        $perfis = array_map(function ($cliente) {
            return ['perfil' => $cliente['perfil'] ?? null];
        }, $clientes);

        $nomes = PessoaNomeHelper::extrairNomes($perfis); // Deve retornar array com 'nome_completo'

        if (count($nomes) > 1) {
            $total = count($nomes);
            return $nomes[0]['nome_completo'] . ' + ' . ($total - 1);
        }

        return $nomes[0]['nome_completo'] ?? 'Nome não encontrado';
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

        foreach ($dataEnv->dados as $movimentacao) {
            $dadosRetorno = new Fluent();

            $dadosRetorno->status = $movimentacao['status']['nome'];
            $dadosRetorno->movimentacao_tipo = $movimentacao['movimentacao_tipo']['nome'];
            $dadosRetorno->valor_movimentado = CurrencyFormatterUtils::toBRL($movimentacao['valor_movimentado']);
            $dadosRetorno->data_movimentacao = (new DateTime($movimentacao['data_movimentacao']))->format('d/m/Y');
            $dadosRetorno->conta = $movimentacao['conta_domain']['conta']['nome'];
            $dadosRetorno->descricao_automatica = $movimentacao['descricao_automatica'];
            $dadosRetorno->observacao = $movimentacao['observacao'];

            $dadosEspecificos = [];

            $referencia = $movimentacao['referencia'];

            switch ($movimentacao['referencia_type']) {

                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    $referenciaPagamento = $referencia['pagamento'];
                    // $dadosEspecificos[] = "NS#{$referenciaPagamento['servico']['numero_servico']}";
                    $cliente = $referenciaPagamento['servico']['cliente'];

                    if (count($cliente)) {
                        $nomes = PessoaNomeHelper::extrairNomes($cliente);
                        $primeiro = $nomes[0]['nome_completo'] ?? '';
                        $quantidadeExtra = count($nomes) - 1;
                        $dadosEspecificos[] = $quantidadeExtra > 0 ? "{$primeiro} + {$quantidadeExtra}" : $primeiro;
                    }

                    $dadosEspecificos[] = $referenciaPagamento['servico']['titulo'];
                    $dadosEspecificos[] = $referenciaPagamento['servico']['area_juridica']['nome'];
                    $dadosEspecificos[] = "NP#{$referenciaPagamento['numero_pagamento']}";
                    break;

                case MovimentacaoContaReferenciaEnum::LANCAMENTO_GERAL->value:
                    $dadosEspecificos[] = $movimentacao['descricao_automatica'];
                    $dadosEspecificos[] = $referencia['categoria']['nome'];
                    $dadosEspecificos[] = "NL#{$referencia['numero_lancamento']}";
                    break;

                case MovimentacaoContaReferenciaEnum::DOCUMENTO_GERADO->value:
                    $dadosEspecificos[] = $movimentacao['descricao_automatica'];
                    $dadosEspecificos[] = "ND#{$referencia['numero_documento']}";
                    break;

                default:
                    break;
            }
            $dadosRetorno->dados_especificos = implode(' - ', $dadosEspecificos);

            $dadosRetorno->created_at = (new DateTime($movimentacao['created_at']))->format('d/m/Y H:i:s');

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
}
