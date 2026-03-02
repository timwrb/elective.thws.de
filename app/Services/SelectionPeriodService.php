<?php

namespace App\Services;

use App\Models\Semester;

class SelectionPeriodService
{
    public function isSelectionOpen(Semester $semester): bool
    {
        return true;
    }
}
