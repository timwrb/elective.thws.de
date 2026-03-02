<div class="space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('fwpm.index', $semester)" wire:navigate>
            {{ __('Back to Selection') }}
        </flux:button>
    </div>

    <div class="space-y-1">
        <flux:heading size="xl">{{ $fwpm->name }}</flux:heading>
        <flux:text class="text-sm">
            {{ $fwpm->module_number }} &middot; {{ $fwpm->credits }} ECTS &middot; {{ $fwpm->language->getLabel() }}
        </flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Left column --}}
        <div class="space-y-6">
            {{-- General info --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-4">{{ __('General Information') }}</flux:heading>
                <dl class="space-y-3">
                    @if ($fwpm->lecturer_name)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Lecturer') }}</dt>
                            <dd class="mt-0.5">{{ $fwpm->lecturer_name }}</dd>
                        </div>
                    @endif
                    @if ($fwpm->professor)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Professor') }}</dt>
                            <dd class="mt-0.5">{{ $fwpm->professor->fullName }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Credits') }}</dt>
                        <dd class="mt-0.5">{{ $fwpm->credits }} ECTS</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Exam Type') }}</dt>
                        <dd class="mt-0.5">{{ $fwpm->exam_type->getLabel() }}</dd>
                    </div>
                    @if ($fwpm->max_participants)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Max Participants') }}</dt>
                            <dd class="mt-0.5">{{ $fwpm->max_participants }}</dd>
                        </div>
                    @endif
                    @if ($fwpm->hours_per_week)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Hours per Week') }}</dt>
                            <dd class="mt-0.5">{{ $fwpm->hours_per_week }} {{ __('hrs/week') }}</dd>
                        </div>
                    @endif
                    @if ($fwpm->type_of_class)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Type of Class') }}</dt>
                            <dd class="mt-0.5">{{ $fwpm->type_of_class }}</dd>
                        </div>
                    @endif
                    @if ($fwpm->recommended_semester)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Recommended Semester') }}</dt>
                            <dd class="mt-0.5">{{ $fwpm->recommended_semester }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Schedule --}}
            @if ($fwpm->schedules->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Schedule') }}</flux:heading>
                    <ul class="space-y-1">
                        @foreach ($fwpm->schedules as $schedule)
                            <li>{{ $schedule->formatted_schedule }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Workload --}}
            @if ($fwpm->total_hours_lectures || $fwpm->total_hours_self_study)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Workload') }}</flux:heading>
                    <dl class="space-y-3">
                        @if ($fwpm->total_hours_lectures)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Lecture Hours') }}</dt>
                                <dd class="mt-0.5">{{ $fwpm->total_hours_lectures }} {{ __('hours') }}</dd>
                            </div>
                        @endif
                        @if ($fwpm->total_hours_self_study)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Self Study') }}</dt>
                                <dd class="mt-0.5">{{ $fwpm->total_hours_self_study }} {{ __('hours') }}</dd>
                            </div>
                        @endif
                        @if ($fwpm->total_hours_lectures && $fwpm->total_hours_self_study)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</dt>
                                <dd class="mt-0.5">{{ $fwpm->total_hours_lectures + $fwpm->total_hours_self_study }} {{ __('hours') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            {{-- Study Programs --}}
            @if ($fwpm->studyPrograms->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Study Programs') }}</flux:heading>
                    <ul class="space-y-1">
                        @foreach ($fwpm->studyPrograms as $program)
                            <li>{{ $program->name_german }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            @if ($fwpm->contents)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Contents') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->contents }}</flux:text>
                </div>
            @endif

            @if ($fwpm->goals)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Goals') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->goals }}</flux:text>
                </div>
            @endif

            @if ($fwpm->literature)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Literature') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->literature }}</flux:text>
                </div>
            @endif

            @if ($fwpm->media)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Media') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->media }}</flux:text>
                </div>
            @endif

            @if ($fwpm->tools)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Tools') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->tools }}</flux:text>
                </div>
            @endif

            @if ($fwpm->prerequisite_recommended)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Prerequisites (Recommended)') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->prerequisite_recommended }}</flux:text>
                </div>
            @endif

            @if ($fwpm->prerequisite_formal)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Prerequisites (Formal)') }}</flux:heading>
                    <flux:text class="whitespace-pre-line">{{ $fwpm->prerequisite_formal }}</flux:text>
                </div>
            @endif

            @if ($fwpm->course_url)
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">{{ __('Course URL') }}</flux:heading>
                    <a href="{{ $fwpm->course_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ $fwpm->course_url }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
