<div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <flux:heading size="xl">{{ __('My AWPF Selection') }}</flux:heading>
                <flux:text>{{ __('Rank your preferred courses. Your top choice will be assigned first.') }}</flux:text>
            </div>
            <flux:link :href="route('awpf.index')" wire:navigate icon="arrow-left">
                {{ __('Browse Courses') }}
            </flux:link>
        </div>

        @if (! $this->settings->enrollmentOpen)
            <flux:callout icon="lock-closed" variant="warning">
                <flux:callout.heading>{{ __('Enrollment is currently closed') }}</flux:callout.heading>
                <flux:callout.text>{{ __('You can view your selections but cannot make changes at this time.') }}</flux:callout.text>
            </flux:callout>
        @endif

        @if (session('status'))
            <flux:callout icon="check-circle" variant="success">
                <flux:callout.text>{{ session('status') }}</flux:callout.text>
            </flux:callout>
        @endif

        @error('rankedIds')
            <flux:callout icon="exclamation-triangle" variant="danger">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        <div class="grid gap-6 lg:grid-cols-2">

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">
                        {{ __('My Ranked Choices') }}
                        <flux:badge size="sm" class="ml-2">
                            {{ count($this->rankedIds) }} / {{ $this->settings->maxSelections }}
                        </flux:badge>
                    </flux:heading>
                </div>

                @if ($this->rankedCourses->isEmpty())
                    <flux:card class="py-10 text-center border-dashed">
                        <flux:icon name="queue-list" class="mx-auto size-10 text-zinc-300" />
                        <flux:text class="mt-2 text-zinc-400">{{ __('Add courses from the list on the right.') }}</flux:text>
                    </flux:card>
                @else
                    <div class="space-y-2">
                        @foreach ($this->rankedCourses as $index => $course)
                            <flux:card class="flex items-center gap-3 py-3">
                                {{-- Priority number --}}
                                <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <span class="text-xs font-bold text-zinc-600 dark:text-zinc-300">{{ $index + 1 }}</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <flux:text class="font-medium truncate">{{ $course->name }}</flux:text>
                                    <div class="flex gap-2 mt-1">
                                        <flux:badge size="sm" variant="pill">{{ $course->credits }} {{ __('CP') }}</flux:badge>
                                        <flux:badge size="sm" variant="pill">{{ $course->language->getLabel() }}</flux:badge>
                                    </div>
                                </div>

                                {{-- Reorder & remove --}}
                                @if ($this->settings->enrollmentOpen)
                                    <div class="flex items-center gap-1 shrink-0">
                                        <flux:button
                                            wire:click="moveUp({{ $index }})"
                                            icon="chevron-up"
                                            size="sm"
                                            variant="ghost"
                                            :disabled="$index === 0"
                                            title="{{ __('Move up') }}"
                                        />
                                        <flux:button
                                            wire:click="moveDown({{ $index }})"
                                            icon="chevron-down"
                                            size="sm"
                                            variant="ghost"
                                            :disabled="$index === $this->rankedCourses->count() - 1"
                                            title="{{ __('Move down') }}"
                                        />
                                        <flux:button
                                            wire:click="removeCourse('{{ $course->id }}')"
                                            icon="x-mark"
                                            size="sm"
                                            variant="ghost"
                                            class="text-red-500 hover:text-red-700"
                                            title="{{ __('Remove') }}"
                                        />
                                    </div>
                                @endif
                            </flux:card>
                        @endforeach
                    </div>
                @endif

                @if ($this->settings->enrollmentOpen)
                    <flux:button
                        wire:click="confirmSave"
                        variant="primary"
                        icon="check"
                        class="w-full"
                        :disabled="count($this->rankedIds) < $this->settings->minRequiredSelections"
                    >
                        {{ __('Save selections') }}
                    </flux:button>
                    @if ($this->settings->minRequiredSelections > 0)
                        <flux:text size="sm" class="text-center text-zinc-400">
                            {{ __('Minimum :min required.', ['min' => $this->settings->minRequiredSelections]) }}
                        </flux:text>
                    @endif
                @endif
            </div>

            <div class="space-y-4">
                <flux:heading size="lg">{{ __('Available Courses') }}</flux:heading>

                @if ($this->availableCourses->isEmpty())
                    <flux:card class="py-10 text-center border-dashed">
                        <flux:icon name="check-circle" class="mx-auto size-10 text-green-400" />
                        <flux:text class="mt-2 text-zinc-400">{{ __('All available courses are in your list.') }}</flux:text>
                    </flux:card>
                @else
                    <div class="space-y-2">
                        @foreach ($this->availableCourses as $course)
                            <flux:card class="flex items-center gap-3 py-3">
                                <div class="flex-1 min-w-0">
                                    <flux:text class="font-medium truncate">{{ $course->name }}</flux:text>
                                    <div class="flex gap-2 mt-1">
                                        <flux:badge size="sm" variant="pill">{{ $course->credits }} {{ __('CP') }}</flux:badge>
                                        <flux:badge size="sm" variant="pill">{{ $course->language->getLabel() }}</flux:badge>
                                        @if (filled($course->formatted_schedules))
                                            <flux:text size="xs" class="text-zinc-400 self-center">{{ $course->formatted_schedules }}</flux:text>
                                        @endif
                                    </div>
                                </div>

                                @if ($this->settings->enrollmentOpen && count($this->rankedIds) < $this->settings->maxSelections)
                                    <flux:button
                                        wire:click="addCourse('{{ $course->id }}')"
                                        icon="plus"
                                        size="sm"
                                        variant="ghost"
                                        class="shrink-0"
                                        title="{{ __('Add to selection') }}"
                                    />
                                @endif
                            </flux:card>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <flux:modal wire:model="showConfirmation" name="confirm-save">
        <div class="mb-4">
            <flux:heading>{{ __('Confirm your selection') }}</flux:heading>
        </div>

        <div class="space-y-4">
            <flux:text>
                {{ __('You are about to save :count course(s) in ranked order. This will replace any previous selections.', ['count' => count($this->rankedIds)]) }}
            </flux:text>

            <ol class="list-decimal list-inside space-y-1 text-sm">
                @foreach ($this->rankedCourses as $course)
                    <li class="text-zinc-700 dark:text-zinc-300">{{ $course->name }}</li>
                @endforeach
            </ol>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <flux:button wire:click="$set('showConfirmation', false)" variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button wire:click="saveSelections" variant="primary">
                {{ __('Confirm') }}
            </flux:button>
        </div>
    </flux:modal>
</div>
