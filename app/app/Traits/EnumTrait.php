<?php

namespace App\Traits;

trait EnumTrait
{
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public static function staticDetailsToArray(): array
    {
        $details = [];
        foreach (self::cases() as $enumValue) {
            $details[] = $enumValue->detalhes();
        }
        return $details;
    }
}
