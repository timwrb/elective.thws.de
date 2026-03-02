<?php

namespace App\Livewire\Fwpm;

use App\Models\Fwpm;
use App\Models\Semester;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class FwpmShow extends Component
{
    public Semester $semester;

    public Fwpm $fwpm;

    public function mount(Semester $semester, Fwpm $fwpm): void
    {
        $this->semester = $semester;
        $this->fwpm = $fwpm->load(['professor', 'schedules', 'studyPrograms']);
    }

    public function render()
    {
        return view('livewire.fwpm.fwpm-show')
            ->title($this->fwpm->name);
    }
}
