<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('research-projects.index') }}">{{ __('Research Projects') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('research-projects.show', $researchProject) }}">{{ $researchProject->title }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('Edit') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl" class="mt-4">{{ __('Edit Research Project') }}</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Title') }}</flux:label>
                <flux:input wire:model="title" required />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea wire:model="description" rows="5" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Supervising Professor') }} <flux:badge color="zinc" size="sm">{{ __('Optional') }}</flux:badge></flux:label>
                <flux:select wire:model="professorId" variant="listbox" searchable clearable :placeholder="__('Search for a professor...')">
                    @foreach($this->professorOptions as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="professorId" />
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button href="{{ route('research-projects.show', $researchProject) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Save Changes') }}</span>
                    <span wire:loading>{{ __('Saving...') }}</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
