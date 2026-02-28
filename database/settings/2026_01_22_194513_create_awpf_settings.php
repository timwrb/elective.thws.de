<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('awpf.maxSelections', 2);
        $this->migrator->add('awpf.enrollmentOpen', false);
        $this->migrator->add('awpf.enrollmentStartDate', null);
        $this->migrator->add('awpf.enrollmentEndDate', null);
        $this->migrator->add('awpf.maxStudentsPerCourse', null);
        $this->migrator->add('awpf.minRequiredSelections', 1);
    }
};
