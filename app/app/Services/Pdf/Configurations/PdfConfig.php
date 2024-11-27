<?php

namespace App\Services\Pdf\Configurations;

use App\Enums\PdfMarginPresetsEnum;

class PdfConfig
{
    public static function getDefaultConfig(): array
    {
        return [
            'paper' => 'A4',
            'orientation' => 'portrait',
            'margins' => PdfMarginPresetsEnum::ESTREITA->value,
        ];
    }
}   
