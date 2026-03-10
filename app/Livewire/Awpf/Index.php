<?php

namespace App\Livewire\Awpf;

use App\Enums\ElectiveStatus;
use App\Enums\ExamType;
use App\Enums\Language;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use App\Settings\AwpfSettings;
use Flux\DateRange;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('AWPF Courses')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $language = '';

    #[Url]
    public string $examType = '';

    public ?DateRange $scheduleRange = null;

    #[Computed]
    public function currentSemester(): ?Semester
    {
        return app(SemesterService::class)->getCurrentSemester();
    }

    #[Computed]
    public function settings(): AwpfSettings
    {
        return app(AwpfSettings::class);
    }

    /** @return LengthAwarePaginator<Awpf> */
    #[Computed]
    public function courses(): LengthAwarePaginator
    {
        return Awpf::query()
            ->where('status', ElectiveStatus::Published)
            ->when($this->currentSemester instanceof Semester, fn ($q) => $q->forSemester($this->currentSemester))
            ->when(filled($this->search), fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when(filled($this->language), fn ($q) => $q->where('language', $this->language))
            ->when(filled($this->examType), fn ($q) => $q->where('exam_type', $this->examType))
            ->when($this->scheduleRange instanceof DateRange, fn ($q) => $q->whereHas(
                'schedules',
                fn ($sq) => $sq->whereBetween('scheduled_at', $this->scheduleRange)
            ))
            ->with(['professor', 'schedules'])
            ->paginate(12);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLanguage(): void
    {
        $this->resetPage();
    }

    public function updatingExamType(): void
    {
        $this->resetPage();
    }

    public function updatingScheduleRange(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function selectionCount(): int
    {
        $semester = $this->currentSemester;

        if (! $semester instanceof Semester) {
            return 0;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return 0;
        }

        return $user->awpfSelections()
            ->wherePivot('semester_id', $semester->id)
            ->count();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function languageOptions(): array
    {
        return collect(Language::cases())
            ->map(fn (Language $lang): array => [
                'value' => $lang->value,
                'label' => $lang->getLabel(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function examTypeOptions(): array
    {
        return collect(ExamType::cases())
            ->map(fn (ExamType $type): array => [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.awpf.index');
    }
}
