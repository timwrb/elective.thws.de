<?php

namespace App\Enums;

enum DegreeField: string
{
    case Science = 'science';
    case Arts = 'arts';
    case Engineering = 'engineering';
    case BusinessAdministration = 'business';

    public function getLabel(): string
    {
        return match ($this) {
            self::Science => __('Science'),
            self::Arts => __('Arts'),
            self::Engineering => __('Engineering'),
            self::BusinessAdministration => __('Business Administration'),
        };
    }

    public function getAbbreviation(): string
    {
        return match ($this) {
            self::Science => 'Sc.',
            self::Arts => 'A.',
            self::Engineering => 'Eng.',
            self::BusinessAdministration => 'BA',
        };
    }
}
