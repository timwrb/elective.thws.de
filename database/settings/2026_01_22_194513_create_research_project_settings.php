<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('research_projects.applicationOpen', false);
        $this->migrator->add('research_projects.applicationStartDate', null);
        $this->migrator->add('research_projects.applicationEndDate', null);
        $this->migrator->add('research_projects.requireApprovalBeforeCreation', true);
        $this->migrator->add('research_projects.maxStudentsPerProject', 1);
    }
};
