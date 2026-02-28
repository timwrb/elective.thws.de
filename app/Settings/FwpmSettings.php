<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FwpmSettings extends Settings
{
    public int $maxSelections;

    public bool $enrollmentOpen;

    public ?string $enrollmentStartDate = null;

    public ?string $enrollmentEndDate = null;

    public ?int $maxStudentsPerCourse = null;

    public int $minRequiredSelections;

    public static function group(): string
    {
        return 'fwpm';
    }
}
