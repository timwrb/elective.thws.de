<x-layouts::app :title="__('Dashboard')">
    @php
        $semesterService = resolve(\App\Services\SemesterService::class);
        $currentSemester = $semesterService->getCurrentSemester();

        $semesters = \App\Models\Semester::query()
            ->whereHas('fwpms', fn ($q) => $q->where('status', \App\Enums\ElectiveStatus::Published))
            ->with(['fwpms' => fn ($q) => $q->where('status', \App\Enums\ElectiveStatus::Published)])
            ->orderByDesc('year')
            ->orderByRaw("CASE WHEN season = 'WS' THEN 1 ELSE 2 END")
            ->get();

        $user = auth()->user();
    @endphp

    <div class="space-y-6">
        <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>

        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Selection Periods') }}</flux:heading>

            @if ($semesters->isEmpty())
                <flux:text>{{ __('No selection periods available.') }}</flux:text>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($semesters as $semester)
                        @php
                            $isCurrent = $currentSemester && $semester->id === $currentSemester->id;
                            $isPast = $currentSemester && $semesterService->isPastSemester($semester);
                            $selectionCount = \App\Models\UserSelection::query()
                                ->forUser($user)
                                ->forSemester($semester)
                                ->fwpm()
                                ->count();
                        @endphp
                        <a
                            href="{{ route('fwpm.index', $semester) }}"
                            wire:navigate
                            class="group rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-600"
                        >
                            <div class="flex items-center justify-between">
                                <flux:heading size="lg">{{ $semester->getLabel() }}</flux:heading>
                                @if ($isCurrent)
                                    <flux:badge variant="success" size="sm">{{ __('Current') }}</flux:badge>
                                @elseif ($isPast)
                                    <flux:badge size="sm">{{ __('Past') }}</flux:badge>
                                @else
                                    <flux:badge variant="warning" size="sm">{{ __('Upcoming') }}</flux:badge>
                                @endif
                            </div>
                            <flux:text class="mt-2 text-sm">
                                {{ trans_choice(':count FWPM|:count FWPMs', $semester->fwpms->count(), ['count' => $semester->fwpms->count()]) }}
                            </flux:text>
                            @if ($selectionCount > 0)
                                <flux:text class="mt-1 text-sm">
                                    {{ trans_choice(':count selection|:count selections', $selectionCount, ['count' => $selectionCount]) }}
                                </flux:text>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
