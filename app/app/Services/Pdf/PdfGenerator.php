<?php

namespace App\Services\Pdf;

use App\Services\Pdf\Configurations\PdfConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use InvalidArgumentException;

class PdfGenerator
{
    protected array $config;

    /**
     * Construtor da classe para inicializar configurações padrões ou customizadas.
     *
     * @param array $customConfig Configurações adicionais (ex.: orientação, tipo de papel, margens).
     */
    public function __construct(array $customConfig = [])
    {
        // Merge entre configurações padrões e customizadas
        $this->config = array_merge(PdfConfig::getDefaultConfig(), $customConfig);
    }

    /**
     * Gera o PDF usando a view Blade e os dados fornecidos.
     *
     * @param string $view Caminho da view Blade (ex.: 'pdf.templates.invoice').
     * @param array $data Dados a serem passados para a view.
     * @param string $output Método de saída do PDF: 'stream', 'download'.
     * @param string|null $fileName Nome do arquivo (usado no download).
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(string $view, array $data, string $output = 'stream', ?string $fileName = 'document.pdf')
    {
        // Renderiza a view e aplica configurações básicas de página
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($this->config['paper'], $this->config['orientation'])
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        // Escolhe o método de saída
        switch ($output) {
            case 'download':
                return $pdf->download($fileName);
            case 'stream':
                return $pdf->stream($fileName);
            default:
                throw new InvalidArgumentException("Invalid output method: {$output}");
        }
    }
}
