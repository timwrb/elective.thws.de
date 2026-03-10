<div>
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <flux:icon name="beaker" class="mx-auto size-12 text-blue-500 mb-4" />

            <flux:heading size="xl">{{ __("You've been invited") }}</flux:heading>
            <flux:subheading class="mt-1 mb-6">{{ __('Join the research project below') }}</flux:subheading>

            <div class="text-left bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 mb-6">
                <p class="font-semibold text-zinc-900 dark:text-white">{{ $researchProject->title }}</p>
                @if($researchProject->description)
                    <p class="text-sm text-zinc-500 mt-1 line-clamp-3">{{ $researchProject->description }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-3">
                    <flux:badge color="blue">{{ $researchProject->credits }} {{ __('credits') }}</flux:badge>
                    @if($researchProject->professor)
                        <flux:badge color="purple" icon="academic-cap">{{ $researchProject->professor->full_name }}</flux:badge>
                    @endif
                </div>
            </div>

            @error('join')
                <flux:callout icon="x-circle" color="red" class="mb-4 text-left">
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            @if($this->isAlreadyMember)
                <flux:callout icon="check-circle" color="green" class="mb-4 text-left">
                    <flux:callout.text>{{ __('You are already a member of this project.') }}</flux:callout.text>
                </flux:callout>
                <flux:button href="{{ route('research-projects.show', $researchProject) }}" variant="primary" class="w-full">
                    {{ __('View Project') }}
                </flux:button>
            @elseif($this->isEnrolledElsewhere)
                <flux:callout icon="x-circle" color="red" class="mb-4 text-left">
                    <flux:callout.text>{{ __('You are already enrolled in another research project this semester. You cannot join multiple projects.') }}</flux:callout.text>
                </flux:callout>
            @elseif(!$this->settings->applicationOpen)
                <flux:callout icon="lock-closed" color="zinc" class="mb-4 text-left">
                    <flux:callout.text>{{ __('Applications are currently closed. You cannot join at this time.') }}</flux:callout.text>
                </flux:callout>
            @else
                <flux:button wire:click="join" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Join Project') }}</span>
                    <span wire:loading>{{ __('Joining...') }}</span>
                </flux:button>
            @endif
        </div>
    </div>
</div>
