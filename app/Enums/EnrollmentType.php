<?php

namespace App\Enums;

enum EnrollmentType: string
{
    case Priority = 'priority';
    case Direct = 'direct';

    public function label(): string
    {
        return match ($this) {
            self::Priority => __('Priority-based'),
            self::Direct => __('Direct Enrollment'),
        };
    }
}
