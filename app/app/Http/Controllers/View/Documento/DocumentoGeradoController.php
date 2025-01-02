<?php

namespace App\Http\Controllers\View\Documento;

use App\Enums\DocumentoGeradoTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PdfMarginPresetsEnum;
use App\Enums\PessoaTipoEnum;
use App\Http\Controllers\Controller;
use App\Models\Documento\DocumentoGerado;
use App\Services\Pdf\PdfGenerator;
use App\Traits\CommonsControllerMethodsTrait;
use App\Utils\CurrencyFormatterUtils;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class DocumentoGeradoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(
        public DocumentoGerado $service,
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
        $dataEnv = new Fluent([
            'dados' => $dados,
            'margins' => PdfMarginPresetsEnum::ESTREITA->detalhes(),
        ]);

        $dataEnv = $this->processedDataRepasseCompensacaoParceiro($dataEnv);

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

        // Processa os dados da busca
        foreach ($dadosParticipantes as $value) {
            $dadosRetorno = new Fluent();

            $dadosRetorno->movimentacao_tipo = $value['parent']['movimentacao_tipo']['nome'];
            $dadosRetorno->valor_parcela = CurrencyFormatterUtils::toBRL($value['parent']['valor_movimentado']);
            $dadosRetorno->valor_participante = CurrencyFormatterUtils::toBRL($value['valor_participante']);
            $dadosRetorno->data_movimentacao = (new DateTime($value['parent']['data_movimentacao']))->format('d/m/Y');
            $dadosRetorno->descricao_automatica = $value['descricao_automatica'];

            $dadosEspecificos = $value['parent']['descricao_automatica'];
            switch ($value['parent']['referencia_type']) {

                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    $referenciaPagamento = $value['parent']['referencia']['pagamento'];

                    $dadosEspecificos .= " - Serviço {$referenciaPagamento['servico']['numero_servico']}";
                    $dadosEspecificos .= " - Pagamento - {$referenciaPagamento['numero_pagamento']}";
                    $dadosEspecificos .= " - {$referenciaPagamento['servico']['area_juridica']['nome']}";
                    $dadosEspecificos .= " - {$referenciaPagamento['servico']['titulo']}";
                    break;

                default:
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
        $dataEnv->mes_ano = Carbon::parse($dadosParticipantes[0]['parent']['data_movimentacao'])->translatedFormat('F/Y');
        $dataEnv->data_documento = Carbon::parse($dataEnv->dados['created_at'])->translatedFormat('d/F/Y');

        $dataEnv->pessoa = $pessoa;

        $dataEnv->total_credito = collect($dadosParticipantes)->where('parent.movimentacao_tipo_id', MovimentacaoContaTipoEnum::CREDITO->value)->sum('valor_participante');

        $dataEnv->total_debito = collect($dadosParticipantes)->where('parent.movimentacao_tipo_id', MovimentacaoContaTipoEnum::DEBITO->value)->sum('valor_participante');

        $dataEnv->total_saldo = CurrencyFormatterUtils::toBRL(bcsub($dataEnv->total_credito, $dataEnv->total_debito, 2));
        $dataEnv->total_credito = CurrencyFormatterUtils::toBRL($dataEnv->total_credito);
        $dataEnv->total_debito = CurrencyFormatterUtils::toBRL($dataEnv->total_debito);

        return $dataEnv;
    }
}
