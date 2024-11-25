<?php

namespace App\Http\Controllers\View\Financeiro;

use App\Http\Controllers\Controller;
use App\Services\Pdf\PdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class FinanceiroController extends Controller
{

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

    public function movimentacaoContaImpressao()
    {
        $pdfService = new PdfGenerator([
            'orientation' => 'portrait',
            'paper' => 'A4',
        ]);

        $invoice = new Fluent([
            'id' =>  '1',
            'date' => '25/11/2024',
            'amount' => '$ 1520,00',
        ]);

        return $pdfService->generate('secao.financeiro.movimentacao-conta.impressao', compact('invoice'));
    }
}
