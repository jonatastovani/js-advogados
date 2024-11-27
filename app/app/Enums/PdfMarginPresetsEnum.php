<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PdfMarginPresetsEnum: string
{
    use EnumTrait;

    case NORMAL = 'normal';
    case ESTREITA = 'estreita';
    case MODERADA = 'moderada';
    case LARGA = 'larga';
    case ESPELHADA = 'espelhada';

    public function detalhes(): array
    {
        return match ($this) {
            self::NORMAL => [
                'id' => self::NORMAL->value,
                'margin_top' => 2.5,  // 2.5 cm
                'margin_right' => 2.5, // 2.5 cm
                'margin_bottom' => 2.5, // 2.5 cm
                'margin_left' => 2.5, // 2.5 cm
            ],
            self::ESTREITA => [
                'id' => self::ESTREITA->value,
                'margin_top' => 1.27,  // 1.27 cm
                'margin_right' => 1.27, // 1.27 cm
                'margin_bottom' => 1.27, // 1.27 cm
                'margin_left' => 1.27, // 1.27 cm
            ],
            self::MODERADA => [
                'id' => self::MODERADA->value,
                'margin_top' => 2.54,  // 2.54 cm
                'margin_right' => 1.91, // 1.91 cm
                'margin_bottom' => 2.54, // 2.54 cm
                'margin_left' => 2.54, // 2.54 cm
            ],
            self::LARGA => [
                'id' => self::LARGA->value,
                'margin_top' => 2.54,  // 2.54 cm
                'margin_right' => 5.08, // 5.08 cm
                'margin_bottom' => 2.54, // 2.54 cm
                'margin_left' => 2.54, // 2.54 cm
            ],
            self::ESPELHADA => [
                'id' => self::ESPELHADA->value,
                'margin_top' => 2.54,  // 2.54 cm
                'margin_inner' => 3.18, // 3.18 cm (interno)
                'margin_outer' => 2.54, // 2.54 cm (externo)
            ],
        };
    }
}
