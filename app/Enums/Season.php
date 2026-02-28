<?php

namespace App\Enums;

enum Season: string
{
    case Winter = 'WS';
    case Summer = 'SS';

    public function getLabel(): string
    {
        return match ($this) {
            self::Winter => __('Winter'),
            self::Summer => __('Summer'),
        };
    }
}
