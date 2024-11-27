<?php

namespace App\Services\Pdf\Configurations;

class PdfConfig
{
    public static function getDefaultConfig(): array
    {
        return [
            'paper' => 'A4',
            'orientation' => 'portrait',
        ];
    }
}   
