<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('research-projects.index')" wire:navigate>Research Projects</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $researchProject->title }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="space-y-2">
            <flux:heading size="xl">{{ $researchProject->title }}</flux:heading>
        </div>

        @if($this->isCreator)
            <flux:button :href="route('research-projects.edit', $researchProject)" wire:navigate variant="ghost" icon="pencil">
                Edit Project
            </flux:button>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Description --}}
            <div class="space-y-3">
                <flux:heading size="lg">Description</flux:heading>
                @if($researchProject->description)
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-sm leading-relaxed">
                        {!! nl2br(e($researchProject->description)) !!}
                    </div>
                @else
                    <flux:text class="text-zinc-400">No description provided.</flux:text>
                @endif
            </div>

            <flux:separator />

            {{-- Team members --}}
            <div class="space-y-4">
                <flux:heading size="lg">Team</flux:heading>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($this->members as $enrollment)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar
                                    size="sm"
                                    :src="$enrollment->user->getAvatarUrl()"
                                    :color="$enrollment->user->getAvatarColor()"
                                    :name="$enrollment->user->name"
                                    :initials="$enrollment->user->initials()"
                                />
                                <div>
                                    <flux:text class="font-medium text-sm">{{ $enrollment->user->full_name }}</flux:text>
                                    <flux:text size="sm" class="text-zinc-400">{{ $enrollment->user->email }}</flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($researchProject->isCreatedBy($enrollment->user))
                                    <flux:badge size="sm" color="zinc">Creator</flux:badge>
                                @endif
                                @if($enrollment->status->value === 'confirmed')
                                    <flux:badge size="sm" color="green" icon="check-circle">Confirmed</flux:badge>
                                @else
                                    <flux:badge size="sm" color="amber" icon="clock">Pending</flux:badge>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Project details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">Project Details</flux:heading>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="credit-card" class="size-4 shrink-0" />
                            <flux:text size="sm">Credits</flux:text>
                        </div>
                        <flux:text size="sm" class="font-medium">{{ $researchProject->credits }} CP</flux:text>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon name="user-group" class="size-4 shrink-0" />
                            <flux:text size="sm">Team size</flux:text>
                        </div>
                        <flux:text size="sm" class="font-medium">{{ $this->members->count() }} / {{ $researchProject->max_students }}</flux:text>
                    </div>

                    @if($researchProject->semester)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="academic-cap" class="size-4 shrink-0" />
                                <flux:text size="sm">Semester</flux:text>
                            </div>
                            <flux:text size="sm" class="font-medium">{{ $researchProject->semester->getLabel() }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Professor --}}
            @if($researchProject->professor)
                <flux:card class="space-y-3">
                    <flux:heading size="sm">Supervising Professor</flux:heading>
                    <div class="flex items-center gap-3">
                        <flux:avatar
                            size="sm"
                            :name="$researchProject->professor->name"
                            :initials="$researchProject->professor->initials()"
                        />
                        <div class="min-w-0">
                            <flux:text class="font-medium text-sm truncate">{{ $researchProject->professor->full_name }}</flux:text>
                            <flux:link href="mailto:{{ $researchProject->professor->email }}" class="text-xs truncate block">
                                {{ $researchProject->professor->email }}
                            </flux:link>
                        </div>
                    </div>
                </flux:card>
            @endif

            {{-- Invite link (creator only) --}}
            @if($this->isCreator)
                <flux:card class="space-y-3">
                    <div>
                        <flux:heading size="sm">Invite Teammates</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 mt-0.5">Share this link to invite students.</flux:text>
                    </div>

                    <div class="space-y-2" x-data="{ copied: false }">
                        <flux:input
                            value="{{ $this->inviteUrl }}"
                            readonly
                            size="sm"
                            class="font-mono text-xs"
                        />
                        <flux:button
                            x-on:click="navigator.clipboard.writeText('{{ $this->inviteUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            variant="filled"
                            icon="clipboard-document"
                            class="w-full"
                            size="sm"
                        >
                            <span x-show="!copied">Copy invite link</span>
                            <span x-show="copied">Copied!</span>
                        </flux:button>
                    </div>

                    <flux:button
                        wire:click="regenerateToken"
                        wire:confirm="This will invalidate the current invite link. Anyone with the old link won't be able to join. Continue?"
                        variant="ghost"
                        icon="arrow-path"
                        size="sm"
                        class="w-full"
                    >
                        Regenerate link
                    </flux:button>
                </flux:card>
            @endif
        </div>
    </div>
</div>
