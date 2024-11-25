<?php

namespace App\Services\Pdf;

use App\Services\Pdf\Configurations\PdfConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use InvalidArgumentException;

class PdfGenerator
{
    protected array $config;

    public function __construct(array $customConfig = [])
    {
        $this->config = array_merge(PdfConfig::getDefaultConfig(), $customConfig);
    }

    /**
     * Gera o PDF usando o template e dados fornecidos.
     *
     * @param string $view Caminho da view Blade (ex: 'pdf.templates.invoice').
     * @param array $data Dados a serem passados para a view.
     * @param string $output Método de saída (inline, stream, download).
     * @param string|null $fileName Nome do arquivo caso seja um download.
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(string $view, array $data, string $output = 'inline', ?string $fileName = 'document.pdf')
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($this->config['paper'], $this->config['orientation'])
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
    
        switch ($output) {
            case 'download':
                return $pdf->download($fileName);
            case 'stream':
            case 'inline': // Ambos são tratados como "stream".
                return $pdf->stream($fileName);
            default:
                throw new InvalidArgumentException("Invalid output method: {$output}");
        }
    }
    
}
