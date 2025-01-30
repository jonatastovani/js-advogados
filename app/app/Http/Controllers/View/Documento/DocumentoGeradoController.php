<?php

namespace App\Http\Controllers\View\Documento;

use App\Enums\BalancoRepasseParceiroTipoParentEnum;
use App\Enums\DocumentoGeradoTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PdfMarginPresetsEnum;
use App\Enums\PessoaTipoEnum;
use App\Http\Controllers\Controller;
use App\Models\Documento\DocumentoGerado;
use App\Services\Financeiro\MovimentacaoContaParticipanteService;
use App\Services\Pdf\PdfGenerator;
use App\Traits\CommonsControllerMethodsTrait;
use App\Utils\CurrencyFormatterUtils;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class DocumentoGeradoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(
        public DocumentoGerado $service,
        public MovimentacaoContaParticipanteService $serviceMovimentacaoContaParticipante
    ) {}

    public function documentoGeradoImpressao(Request $request)
    {
        $dados = DocumentoGerado::with('documento_gerado_tipo')->find($request->uuid);

        switch ($dados->documento_gerado_tipo_id) {
            case DocumentoGeradoTipoEnum::REPASSE_PARCEIRO->value:
                return $this->repasseCompensacaoParceiro($dados->toArray());
        }
    }

    private function repasseCompensacaoParceiro($dados)
    {
        $somatorias = $this->serviceMovimentacaoContaParticipante->obterTotaisParticipacoes(collect($dados['dados']['dados_participacao']));

        $dataEnv = new Fluent([
            'dados' => $dados,
            'somatorias' => $somatorias,
            'margins' => PdfMarginPresetsEnum::ESTREITA->detalhes(),
        ]);

        try {
            $dataEnv = $this->processedDataRepasseCompensacaoParceiro($dataEnv);
        } catch (\Throwable $th) {
            dd($th);
        }
        // Configurações personalizadas de PDF
        $pdfService = new PdfGenerator([
            'orientation' => 'landscape',
            'paper' => 'A4',
        ]);

        return $pdfService->generate('secao.documento.repasse-compensacao-parceiro.impressao', compact('dataEnv'));
    }

    private function processedDataRepasseCompensacaoParceiro(Fluent $dataEnv)
    {
        $processedData = [];

        //Dados do fluent
        $dados = $dataEnv->dados;
        $campoDadosDocumentoGerado = $dados['dados'];
        $dadosParticipantes = $campoDadosDocumentoGerado['dados_participacao'];

        $mes_ano_movimentacao = null;

        // Processa os dados da busca
        foreach ($dadosParticipantes as $participacao) {
            $dadosRetorno = new Fluent();

            $parent = $participacao['parent'];
            $dadosRetorno->valor_participante = CurrencyFormatterUtils::toBRL($participacao['valor_participante']);
            $dadosRetorno->descricao_automatica = $participacao['descricao_automatica'];

            switch ($participacao['parent_type']) {

                case BalancoRepasseParceiroTipoParentEnum::MOVIMENTACAO_CONTA->value:

                    if (!$mes_ano_movimentacao) {
                        $mes_ano_movimentacao = $parent['data_movimentacao'];
                    }
                    $dadosRetorno->data_movimentacao = (new DateTime($parent['data_movimentacao']))->format('d/m/Y');
                    $dadosRetorno->movimentacao_tipo = $parent['movimentacao_tipo']['nome'];
                    $dadosRetorno->valor_parcela = CurrencyFormatterUtils::toBRL($parent['valor_movimentado']);

                    $dadosEspecificos = $parent['descricao_automatica'];

                    switch ($parent['referencia_type']) {

                        case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                            $referenciaPagamento = $parent['referencia']['pagamento'];

                            $dadosEspecificos .= " - Serviço {$referenciaPagamento['servico']['numero_servico']}";
                            $dadosEspecificos .= " - Pagamento - {$referenciaPagamento['numero_pagamento']}";
                            $dadosEspecificos .= " - {$referenciaPagamento['servico']['area_juridica']['nome']}";
                            $dadosEspecificos .= " - {$referenciaPagamento['servico']['titulo']}";
                            break;

                        default:
                            break;
                    }
                    break;

                case BalancoRepasseParceiroTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value:

                    if (!$mes_ano_movimentacao) {
                        // dump($parent);
                        $mes_ano_movimentacao = $parent['data_vencimento'];
                    }

                    $dadosRetorno->data_movimentacao = (new DateTime($parent['data_vencimento']))->format('d/m/Y');
                    $dadosRetorno->movimentacao_tipo = $parent['parceiro_movimentacao_tipo']['nome'];

                    $dadosEspecificos = $participacao['descricao_automatica'];
                    $dadosEspecificos .= " - NR#{$parent['numero_ressarcimento']}";
                    $dadosEspecificos .= " - ({$parent['categoria']['nome']})";
                    $dadosEspecificos .= " - {$parent['descricao']}";
                    break;

                default:
                    throw new Exception('Tipo parent de registro de balanço de parceiro não configurado.', 500);
                    break;
            }

            $dadosRetorno->dados_especificos = $dadosEspecificos;

            $processedData[] = $dadosRetorno->toArray();
        }

        // Cria a chave `processedData` no objeto Fluent
        $dataEnv->processedData = $processedData;

        $nomeParticipante = '';
        $pessoa = $dadosParticipantes[0]['referencia']['pessoa'];
        switch ($pessoa['pessoa_dados_type']) {
            case PessoaTipoEnum::PESSOA_FISICA->value:
                $nomeParticipante = $pessoa['pessoa_dados']['nome'];
                break;

            case PessoaTipoEnum::PESSOA_JURIDICA->value:
                $nomeParticipante = $pessoa['pessoa_dados']['nome_fantasia'];
                break;
        }

        $dataEnv->title = $dataEnv->dados['documento_gerado_tipo']['nome'];
        $dataEnv->nome_participante = $nomeParticipante;
        $dataEnv->mes_ano = Carbon::parse($mes_ano_movimentacao)->translatedFormat('F/Y');
        $dataEnv->data_documento = Carbon::parse($dataEnv->dados['created_at'])->translatedFormat('d/F/Y');

        $dataEnv->somatorias = CurrencyFormatterUtils::convertArrayToBRL($dataEnv->somatorias->toArray());

        $dataEnv->pessoa = $pessoa;

        return $dataEnv;
    }
}
