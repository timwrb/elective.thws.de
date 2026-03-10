<div
    x-data="{
        choices: $persist([]).as('fwpm-choices-{{ $semester->id }}'),
        desiredCount: $persist(1).as('fwpm-desired-{{ $semester->id }}'),
        savedChoices: [],

        get minChoices() { return this.desiredCount },
        get maxChoices() { return this.desiredCount + 2 },
        get canSave() {
            return this.choices.length >= this.minChoices
                && this.choices.length <= this.maxChoices
        },
        get canAddMore() { return this.choices.length < this.maxChoices },
        get hasUnsavedChanges() {
            if (this.choices.length !== this.savedChoices.length) return true
            return this.choices.some((id, i) => id !== this.savedChoices[i])
        },

        isSelected(id) { return this.choices.includes(id) },
        add(id) {
            if (!this.isSelected(id) && this.canAddMore) {
                this.choices.push(id)
            }
        },
        remove(id) { this.choices = this.choices.filter(c => c !== id) },
        moveUp(i) {
            if (i > 0) {
                const temp = this.choices[i]
                this.choices[i] = this.choices[i - 1]
                this.choices[i - 1] = temp
                this.choices = [...this.choices]
            }
        },
        moveDown(i) {
            if (i < this.choices.length - 1) {
                const temp = this.choices[i]
                this.choices[i] = this.choices[i + 1]
                this.choices[i + 1] = temp
                this.choices = [...this.choices]
            }
        },

        dragIndex: null,
        dragStart(i) { this.dragIndex = i },
        dragOver(e, i) {
            e.preventDefault()
            if (this.dragIndex === null || this.dragIndex === i) return
            const item = this.choices.splice(this.dragIndex, 1)[0]
            this.choices.splice(i, 0, item)
            this.dragIndex = i
            this.choices = [...this.choices]
        },
        dragEnd() { this.dragIndex = null },

        async save() {
            await $wire.saveSelections(this.choices, this.desiredCount)
        },

        getFwpm(id) {
            return this.availableFwpms.find(f => f.id === id)
        },

        init() {
            this.availableFwpms = @js($this->availableFwpms->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
            ])->values()->all())

            const saved = $wire.savedSelections
            this.savedChoices = [...saved]
            if (this.choices.length === 0 && saved.length > 0) {
                this.choices = [...saved]
            }
        }
    }"
    x-on:selections-saved.window="savedChoices = [...$wire.savedSelections]; choices = [...$wire.savedSelections]"
    class="mx-auto max-w-5xl space-y-6"
>
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('FWPM Selection') }} — {{ $semester->getLabel() }}</flux:heading>
            @if ($this->isPastSemester)
                <flux:text class="mt-1">{{ __('Your past selection') }}</flux:text>
            @endif
        </div>
    </div>

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-triangle">
            {{ session('error') }}
        </flux:callout>
    @endif

    @if ($this->isPastSemester)
        {{-- Past semester: read-only view --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">{{ __('Your Choices') }}</flux:heading>

            @if ($this->savedSelectionsWithStatus->isEmpty())
                <flux:text>{{ __('No selections were made for this semester.') }}</flux:text>
            @else
                <div class="space-y-3">
                    @foreach ($this->savedSelectionsWithStatus as $index => $selection)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium dark:bg-zinc-800">{{ $index + 1 }}</span>
                                <span>{{ $selection->elective->name }}</span>
                            </div>
                            <flux:badge :variant="$selection->status->color()" size="sm">
                                {{ $selection->status->label() }}
                            </flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        {{-- Active mode --}}

        {{-- Desired count selector --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-3">{{ __('How many FWPMs do you want to take?') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                @for ($i = 1; $i <= 5; $i++)
                    <button
                        type="button"
                        x-on:click="desiredCount = {{ $i }}"
                        :class="desiredCount === {{ $i }}
                            ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
                            : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'"
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium transition"
                    >
                        {{ $i }}
                    </button>
                @endfor
            </div>
            <flux:text class="mt-2 text-sm">
                {{ __('You must select between') }}
                <span x-text="minChoices"></span>–<span x-text="maxChoices"></span>
                {{ __('choices') }}.
            </flux:text>
        </div>

        {{-- Selected choices --}}
        <div class="rounded-xl border-2 border-dotted border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-600 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Your Choices') }}</flux:heading>
                <flux:text class="text-sm">
                    <span x-text="choices.length"></span> / <span x-text="minChoices"></span>–<span x-text="maxChoices"></span>
                </flux:text>
            </div>

            <template x-if="choices.length === 0">
                <flux:text class="py-4 text-center">{{ __('No FWPMs selected yet. Browse the list below and add your choices.') }}</flux:text>
            </template>

            <div class="space-y-2">
                <template x-for="(choiceId, index) in choices" :key="choiceId">
                    <div
                        class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700"
                        draggable="true"
                        x-on:dragstart="dragStart(index)"
                        x-on:dragover="dragOver($event, index)"
                        x-on:dragend="dragEnd()"
                        :class="dragIndex === index ? 'opacity-50' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-sm font-medium dark:bg-zinc-800" x-text="index + 1"></span>
                            <span x-text="getFwpm(choiceId)?.name ?? choiceId"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                x-on:click="moveUp(index)"
                                x-show="index > 0"
                                class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                :title="'{{ __('Move up') }}'"
                            >
                                <flux:icon.chevron-up class="size-4" />
                            </button>
                            <button
                                type="button"
                                x-on:click="moveDown(index)"
                                x-show="index < choices.length - 1"
                                class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                :title="'{{ __('Move down') }}'"
                            >
                                <flux:icon.chevron-down class="size-4" />
                            </button>
                            <button
                                type="button"
                                x-on:click="remove(choiceId)"
                                class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
                                :title="'{{ __('Remove') }}'"
                            >
                                <flux:icon.x-mark class="size-4" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 space-y-3" x-show="choices.length > 0" x-cloak>
                <div x-show="hasUnsavedChanges" x-transition.opacity class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-800 dark:border-amber-800/40 dark:bg-amber-950/30 dark:text-amber-200">
                    <flux:icon.exclamation-triangle class="mt-0.5 size-5 shrink-0" />
                    <span>{{ __('You have unsaved changes. Please save your selection. You can adjust your choices at any time during the selection period.') }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button
                        variant="primary"
                        x-on:click="save()"
                        x-bind:disabled="!canSave"
                    >
                        {{ __('Save Selection') }}
                    </flux:button>
                    <flux:text x-show="!canSave" class="text-sm text-amber-600 dark:text-amber-400">
                        {{ __('You need at least') }} <span x-text="minChoices"></span> {{ __('choices') }}.
                    </flux:text>
                </div>
            </div>
        </div>

        {{-- Available FWPMs --}}
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">{{ __('Available FWPMs') }}</flux:heading>

            <div class="mb-4">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" :placeholder="__('Search FWPMs...')" />
            </div>

            @if ($this->availableFwpms->isEmpty())
                <flux:text class="py-4 text-center">{{ __('No FWPMs available for this semester.') }}</flux:text>
            @else
                <div class="space-y-3">
                    @foreach ($this->availableFwpms as $fwpm)
                        <div wire:key="fwpm-{{ $fwpm->id }}" class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <div class="min-w-0 flex-1">
                                    <span class="font-medium">{{ $fwpm->name }}</span>
                                    <flux:text class="mt-1 text-sm">
                                        @if ($fwpm->lecturer_name)
                                            {{ $fwpm->lecturer_name }}
                                        @elseif ($fwpm->professor)
                                            {{ $fwpm->professor->fullName }}
                                        @endif
                                        @if ($fwpm->schedules->isNotEmpty())
                                            &middot; {{ $fwpm->schedules->pluck('formatted_schedule')->join(', ') }}
                                        @endif
                                    </flux:text>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <flux:button size="sm" variant="ghost" :href="route('fwpm.show', [$semester, $fwpm])" wire:navigate>
                                        {{ __('Details') }}
                                    </flux:button>
                                    <div class="w-24">
                                        <template x-if="isSelected('{{ $fwpm->id }}')">
                                            <flux:button size="sm" variant="ghost" class="w-full" disabled>
                                                {{ __('Selected') }}
                                            </flux:button>
                                        </template>
                                        <template x-if="!isSelected('{{ $fwpm->id }}')">
                                            <flux:button
                                                size="sm"
                                                variant="primary"
                                                class="w-full"
                                                x-on:click="add('{{ $fwpm->id }}')"
                                                x-bind:disabled="!canAddMore"
                                            >
                                                {{ __('Add') }}
                                            </flux:button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    @endif
</div>
