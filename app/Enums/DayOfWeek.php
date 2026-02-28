<?php

namespace App\Enums;

enum DayOfWeek: string
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';

    public function getLabel(): string
    {
        return match ($this) {
            self::Monday => __('Monday'),
            self::Tuesday => __('Tuesday'),
            self::Wednesday => __('Wednesday'),
            self::Thursday => __('Thursday'),
            self::Friday => __('Friday'),
            self::Saturday => __('Saturday'),
            self::Sunday => __('Sunday'),
        };
    }

    public function getAbbreviation(): string
    {
        return match ($this) {
            self::Monday => 'Mo',
            self::Tuesday => 'Di',
            self::Wednesday => 'Mi',
            self::Thursday => 'Do',
            self::Friday => 'Fr',
            self::Saturday => 'Sa',
            self::Sunday => 'So',
        };
    }
}
