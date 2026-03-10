# Research Project Document Uploads — Design

**Date:** 2026-03-10

## Overview

Allow confirmed members of a research project to upload, view, and delete documents related to their project.

## Constraints

| Rule | Value |
|---|---|
| Max documents per project | 10 |
| Max file size | 10 MB |
| Allowed types | `pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx` |
| Upload permission | Confirmed members only |
| Delete permission | Uploader only |

## Architecture

- **Storage:** Spatie MediaLibrary — existing `attachments` collection on `public` disk (already registered on `ResearchProject` model).
- **New Livewire component:** `app/Livewire/ResearchProject/Documents.php` with view `resources/views/livewire/research-project/documents.blade.php`.
- **Embedded in:** `show.blade.php` via `<livewire:research-project.documents :project="$researchProject" />`.
- **Uploader tracking:** `custom_properties->uploaded_by` stores the uploader's user ID on each media record.

## Component Behaviour

### Upload
- `flux:input type="file"` with an "Upload Document" button.
- Validates: required, max 10 MB, allowed MIME types.
- Checks: project has fewer than 10 documents.
- Stores via `$project->addMedia($file)->withCustomProperties(['uploaded_by' => auth()->id()])->toMediaCollection('attachments')`.

### List
- Document rows showing: icon (by file type), filename, file size, upload date.
- Delete button visible only to the uploader.
- Empty state when no documents uploaded.

### Delete
- Confirmation via `flux:modal` before deletion.
- Verifies `media->getCustomProperty('uploaded_by') === auth()->id()`.

## Testing

- Feature test: confirmed member can upload a document.
- Feature test: non-member cannot upload.
- Feature test: uploader can delete their own document.
- Feature test: non-uploader cannot delete another's document.
- Feature test: uploading an 11th document is rejected.