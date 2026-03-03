<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('AWPF Courses') }}</flux:heading>
            <flux:text>{{ __('Allgemeine Wahlpflichtfächer — browse and select your courses.') }}</flux:text>
        </div>

        <flux:button
            :href="route('awpf.select')"
            wire:navigate
            icon="list-bullet"
            variant="primary"
        >
            {{ __('My Selection') }}
            @if ($this->selectionCount > 0)
                <flux:badge size="sm" class="ml-1">{{ $this->selectionCount }}</flux:badge>
            @endif
        </flux:button>
    </div>

    @if ($this->settings->enrollmentOpen)
        <flux:callout icon="information-circle" variant="success">
            <flux:callout.heading>{{ __('Enrollment is open') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Rank your course preferences on the') }}
                <flux:link :href="route('awpf.select')" wire:navigate>{{ __('selection page') }}</flux:link>.
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="flex flex-wrap gap-3">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search courses…') }}"
            icon="magnifying-glass"
            class="max-w-xs"
            clearable
        />

        <flux:select wire:model.live="language" class="max-w-[160px]" placeholder="{{ __('All languages') }}">
            <flux:select.option value="">{{ __('All languages') }}</flux:select.option>
            @foreach ($this->languageOptions as $option)
                <flux:select.option :value="$option['value']">{{ $option['label'] }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="examType" class="max-w-[180px]" placeholder="{{ __('All exam types') }}">
            <flux:select.option value="">{{ __('All exam types') }}</flux:select.option>
            @foreach ($this->examTypeOptions as $option)
                <flux:select.option :value="$option['value']">{{ $option['label'] }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:date-picker
            wire:model.live="scheduleRange"
            mode="range"
            placeholder="{{ __('Schedule timeframe') }}"
            clearable
        />
    </div>

    @if ($this->courses->isEmpty())
        <flux:card class="py-16 text-center">
            <flux:icon name="academic-cap" class="mx-auto size-12 text-zinc-400" />
            <flux:heading class="mt-4">{{ __('No courses found') }}</flux:heading>
            <flux:text>{{ __('Try adjusting your search or language filter.') }}</flux:text>
        </flux:card>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->courses as $course)
                <flux:card class="flex flex-col gap-4 hover:shadow-md transition-shadow">

                    <div class="flex items-start justify-between gap-2 flex-1">
                        <flux:heading size="lg" class="leading-tight">{{ $course->name }}</flux:heading>
{{--                            <flux:badge :color="$course->status->getColor()" size="sm" inset="top">--}}
{{--                                {{ $course->status->getLabel() }}--}}
{{--                            </flux:badge>--}}
                    </div>

                    @if ($course->professor)
                        <div class="flex items-center gap-2">
                            <flux:avatar
                                size="xs"
                                :name="$course->professor->name"
                                :initials="$course->professor->initials()"
                            />
                            <flux:text size="sm">{{ $course->professor->full_name }}</flux:text>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        <flux:badge size="sm" icon="language" variant="pill">
                            {{ $course->language->getLabel() }}
                        </flux:badge>
                        <flux:badge size="sm" icon="pencil-square" variant="pill">
                            {{ $course->exam_type->getShortLabel() }}
                        </flux:badge>
                    </div>

                    @if (filled($course->formatted_schedules))
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="clock" class="inline size-4 align-middle mr-1" />
                            {{ $course->formatted_schedules }}
                        </flux:text>
                    @endif

                    <flux:button
                        :href="route('awpf.show', $course)"
                        wire:navigate
                        variant="filled"
                        icon-trailing="arrow-right"
                        class="place-self-end justify-between"
                    >
                        {{ __('View Details') }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    @endif

    <flux:pagination :paginator="$this->courses" scroll-to />
</div>
