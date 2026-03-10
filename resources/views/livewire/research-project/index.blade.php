<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">{{ __('Research Projects') }}</flux:heading>
                <flux:subheading>{{ __('Projects you are part of') }}</flux:subheading>
            </div>

            @if($this->settings->applicationOpen && !$this->isEnrolledInAnyProject)
                <flux:button href="{{ route('research-projects.create') }}" icon="plus" variant="primary">
                    {{ __('Create Project') }}
                </flux:button>
            @endif
        </div>

        @if($this->settings->applicationOpen && !$this->isEnrolledInAnyProject)
            <flux:callout icon="information-circle" variant="success" class="mb-6">
                <flux:callout.heading>{{ __('Applications are open') }}</flux:callout.heading>
                <flux:callout.text>{{ __('You can create a new research project or join one via an invite link.') }}</flux:callout.text>
            </flux:callout>
        @endif

        @if($this->projects->isEmpty())
            <flux:card class="py-16 text-center">
                <flux:icon name="beaker" class="mx-auto size-12 text-zinc-400" />
                <flux:heading class="mt-4">{{ __('No projects yet') }}</flux:heading>
                <flux:text>
                    @if($this->settings->applicationOpen)
                        {{ __('Create a project or ask a teammate to share their invite link.') }}
                    @else
                        {{ __('You have not been enrolled in any research project.') }}
                    @endif
                </flux:text>
            </flux:card>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->projects as $project)
                    <flux:card class="flex flex-col gap-4 hover:shadow-md transition-shadow">

                        <div class="flex items-start justify-between gap-2 flex-1">
                            <flux:heading size="lg" class="leading-tight">{{ $project->title }}</flux:heading>
                        </div>

                        @if($project->professor)
                            <div class="flex items-center gap-2">
                                <flux:avatar
                                    size="xs"
                                    :name="$project->professor->name"
                                    :initials="$project->professor->initials()"
                                />
                                <flux:text size="sm">{{ $project->professor->full_name }}</flux:text>
                            </div>
                        @endif

                        @if($project->description)
                            <flux:text size="sm" class="line-clamp-2">{{ $project->description }}</flux:text>
                        @endif

                        @php
                            $members = $project->enrollments
                                ->whereIn('status', ['pending', 'confirmed'])
                                ->take(4);
                            $overflow = max(0, $project->enrollments->whereIn('status', ['pending', 'confirmed'])->count() - 4);
                        @endphp

                        <flux:avatar.group>
                            @foreach($members as $enrollment)
                                <flux:avatar
                                    circle
                                    size="sm"
                                    :src="$enrollment->user->getAvatarUrl()"
                                    :color="$enrollment->user->getAvatarColor()"
                                    :name="$enrollment->user->name"
                                    :initials="$enrollment->user->initials()"
                                />
                            @endforeach
                            @if($overflow > 0)
                                <flux:avatar circle size="sm">+{{ $overflow }}</flux:avatar>
                            @endif
                        </flux:avatar.group>

                        <flux:button
                            :href="route('research-projects.show', $project)"
                            wire:navigate
                            variant="filled"
                            icon-trailing="arrow-right"
                            class="place-self-end justify-between"
                        >
                            {{ __('View Project') }}
                        </flux:button>
                    </flux:card>
                @endforeach
            </div>

        @endif
    </div>
</div>
