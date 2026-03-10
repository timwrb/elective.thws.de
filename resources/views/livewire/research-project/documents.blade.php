<div class="space-y-4">

    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Documents') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-400">
            {{ $this->documents->count() }} / 10
        </flux:text>
    </div>

    {{-- Upload form --}}
    @if($this->documents->count() < 10)
        <form wire:submit="storeDocument" class="space-y-3">
            <flux:file-upload
                wire:model="file"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
            >
                <flux:file-upload.dropzone
                    heading="{{ __('Drop document or click to browse') }}"
                    text="{{ __('PDF, Word, Excel, PowerPoint · max 10 MB') }}"
                    inline
                    with-progress
                />
            </flux:file-upload>

            @if ($file)
                <div class="flex flex-col gap-2 mt-2">
                    <flux:file-item
                        :heading="$file->getClientOriginalName()"
                        :size="$file->getSize()"
                    >
                        <x-slot name="actions">
                            <flux:file-item.remove wire:click="removeFile" />
                        </x-slot>
                    </flux:file-item>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button
                        type="submit"
                        size="sm"
                        variant="filled"
                        icon="arrow-up-tray"
                        wire:loading.attr="disabled"
                        wire:target="storeDocument"
                    >
                        <span wire:loading.remove wire:target="storeDocument">{{ __('Upload') }}</span>
                        <span wire:loading wire:target="storeDocument">{{ __('Uploading…') }}</span>
                    </flux:button>
                </div>
            @endif

            @error('file')
                <flux:text size="sm" class="text-red-500">{{ $message }}</flux:text>
            @enderror
        </form>
    @else
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Upload limit reached') }}</flux:callout.heading>
            <flux:callout.text>{{ __('This project has reached the maximum of 10 documents. Delete an existing document to upload a new one.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Document list --}}
    @if($this->documents->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-center border border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
            <flux:icon name="document" class="size-8 text-zinc-300 dark:text-zinc-600 mb-2" />
            <flux:text class="text-zinc-400">{{ __('No documents uploaded yet.') }}</flux:text>
        </div>
    @else
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden">
            @foreach($this->documents as $document)
                <div wire:key="document-{{ $document->id }}" class="flex items-center justify-between gap-3 px-4 py-3 bg-white dark:bg-zinc-900">
                    <div class="flex items-center gap-3 min-w-0">
                        <flux:icon name="document-text" class="size-5 text-zinc-400 shrink-0" />
                        <div class="min-w-0">
                            <flux:text class="font-medium text-sm truncate">{{ $document->file_name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-400">
                                {{ number_format($document->size / 1024 / 1024, 2) }} MB
                                · {{ $document->created_at->diffForHumans() }}
                            </flux:text>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <flux:button
                            :href="$document->getUrl()"
                            target="_blank"
                            rel="noopener noreferrer"
                            size="sm"
                            variant="ghost"
                            icon="arrow-down-tray"
                        />
                        @if($document->getCustomProperty('uploaded_by') === auth()->id())
                            <flux:button
                                wire:click="confirmDelete({{ $document->id }})"
                                wire:loading.attr="disabled"
                                wire:target="confirmDelete({{ $document->id }})"
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                class="text-red-500 hover:text-red-600"
                            />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model="confirmDeleteId" name="confirm-delete-document">
        <div class="space-y-4">
            <flux:heading>{{ __('Delete Document') }}</flux:heading>
            <flux:text>{{ __('Are you sure you want to delete this document? This action cannot be undone.') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('confirmDeleteId', null)" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="deleteDocument({{ $confirmDeleteId ?? 0 }})" variant="danger">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
