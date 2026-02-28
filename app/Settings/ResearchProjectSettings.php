<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ResearchProjectSettings extends Settings
{
    public bool $applicationOpen;

    public ?string $applicationStartDate = null;

    public ?string $applicationEndDate = null;

    public bool $requireApprovalBeforeCreation;

    public int $maxStudentsPerProject;

    public static function group(): string
    {
        return 'research_projects';
    }
}
