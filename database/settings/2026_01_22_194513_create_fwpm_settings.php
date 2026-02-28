<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('fwpm.maxSelections', 3);
        $this->migrator->add('fwpm.enrollmentOpen', false);
        $this->migrator->add('fwpm.enrollmentStartDate', null);
        $this->migrator->add('fwpm.enrollmentEndDate', null);
        $this->migrator->add('fwpm.maxStudentsPerCourse', null);
        $this->migrator->add('fwpm.minRequiredSelections', 1);
    }
};
