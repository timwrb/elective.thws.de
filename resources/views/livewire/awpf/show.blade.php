<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <flux:link :href="route('awpf.index')" wire:navigate icon="arrow-left">
        <- {{ __('All AWPF Courses') }}
    </flux:link>

    <flux:spacer/>

    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="space-y-2">
            <flux:heading size="xl">{{ $awpf->name }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                <flux:badge :color="$awpf->status->getColor()">{{ $awpf->status->getLabel() }}</flux:badge>
                <flux:badge variant="pill" icon="credit-card">{{ $awpf->credits }} {{ __('CP') }}</flux:badge>
                <flux:badge variant="pill" icon="language">{{ $awpf->language->getLabel() }}</flux:badge>
                <flux:badge variant="pill" icon="pencil-square">{{ $awpf->exam_type->getLabel() }}</flux:badge>
            </div>
        </div>

        @if ($this->settings->enrollmentOpen)
            @if ($this->isInUserSelection)
                <flux:button
                    :href="route('awpf.select')"
                    wire:navigate
                    variant="primary"
                    icon="check-circle"
                >
                    {{ __('In your list — priority #:n', ['n' => $this->userPriority]) }}
                </flux:button>
            @else
                <flux:button
                    :href="route('awpf.select')"
                    wire:navigate
                    variant="primary"
                    icon="plus"
                >
                    {{ __('Add to my selection') }}
                </flux:button>
            @endif
        @else
            <flux:button variant="ghost" disabled icon="lock-closed">
                {{ __('Enrollment is closed') }}
            </flux:button>
        @endif
    </div>

    <flux:separator />

    <div class="grid gap-8 lg:grid-cols-3">

        <div class="lg:col-span-2 space-y-4">
            <flux:heading size="lg">{{ __('Course Description') }}</flux:heading>
            @if (filled($awpf->content))
                <div class="prose prose-zinc dark:prose-invert max-w-none text-sm leading-relaxed">
                    {!! nl2br(e($awpf->content)) !!}
                </div>
            @else
                <flux:text class="text-zinc-400">{{ __('No description available.') }}</flux:text>
            @endif

            @if (filled($awpf->course_url))
                <flux:button :href="$awpf->course_url" target="_blank" rel="noopener noreferrer"  icon="arrow-top-right-on-square" variant="ghost" class="mt-2">
                    {{ __('Course Website') }}
                </flux:button>
            @endif
        </div>

        <div class="space-y-6">
            @if ($awpf->professor)
                <flux:card>
                    <flux:heading size="sm" class="mb-3">{{ __('Lecturer') }}</flux:heading>
                    <div class="flex items-center gap-3">
                        <flux:avatar
                            :name="$awpf->professor->name"
                            :initials="$awpf->professor->initials()"
                        />
                        <div>
                            <flux:text class="font-medium">{{ $awpf->professor->full_name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500">{{ $awpf->professor->email }}</flux:text>
                        </div>
                    </div>
                </flux:card>
            @endif

            @if ($awpf->schedules->isNotEmpty())
                <flux:card>
                    <flux:heading size="sm" class="mb-3">{{ __('Schedule') }}</flux:heading>
                    <div class="space-y-2">
                        @foreach ($awpf->schedules->sortBy('scheduled_at') as $schedule)
                            <div class="flex items-center gap-2 text-sm">
                                <flux:icon name="clock" class="size-4 text-zinc-400 shrink-0" />
                                <flux:text size="sm">{{ $schedule->formatted_schedule }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif

        </div>
    </div>
</div>
