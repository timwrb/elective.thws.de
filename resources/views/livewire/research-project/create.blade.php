<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('research-projects.index') }}">Research Projects</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl" class="mt-4">Create Research Project</flux:heading>
            <flux:subheading>Describe your project. You can invite teammates after creation.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="title" placeholder="e.g. AI-based Traffic Optimization" required />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea wire:model="description" placeholder="Describe the research goals, methodology, and expected outcomes..." rows="5" />
                <flux:error name="description" />
            </flux:field>

            <flux:field>
                <flux:label>Supervising Professor <flux:badge color="zinc" size="sm">Optional</flux:badge></flux:label>
                <flux:select wire:model="professorId" variant="listbox" searchable clearable placeholder="Search for a professor...">
                    @foreach($this->professorOptions as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="professorId" />
            </flux:field>

            <flux:callout icon="information-circle" color="zinc">
                <flux:callout.text>
                    The project is assigned <strong>{{ $this->settings->defaultCredits }} credits</strong>,
                    allows up to <strong>{{ $this->settings->maxStudentsPerProject }} students</strong>,
                    and runs for the duration of the current semester.
                </flux:callout.text>
            </flux:callout>

            <div class="flex justify-end gap-3">
                <flux:button href="{{ route('research-projects.index') }}" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Create Project</span>
                    <span wire:loading>Creating...</span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
