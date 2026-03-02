<?php

use App\Models\Semester;
use App\Services\SelectionPeriodService;

test('isSelectionOpen returns true', function () {
    $semester = Semester::factory()->winter()->year(2025)->create();

    $service = app(SelectionPeriodService::class);

    expect($service->isSelectionOpen($semester))->toBeTrue();
});
