<?php

namespace App\Enums;

enum Language: string
{
    case English = 'English';
    case German = 'Deutsch';

    public function getLabel(): string
    {
        return match ($this) {
            self::English => __('English'),
            self::German => __('Deutsch'),
        };
    }
}
