<?php

namespace App\Services\Pdf\Configurations;

class PdfConfig
{
    public static function getDefaultConfig(): array
    {
        return [
            'paper' => 'A4',
            'orientation' => 'portrait',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 20,
            'margin_right' => 20,
        ];
    }
}   
