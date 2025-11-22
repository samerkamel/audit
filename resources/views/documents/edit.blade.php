@extends('layouts/layoutMaster')

@section('title', __('Edit Document'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">{{ __('Documents') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('documents.show', $document) }}">{{ $document->document_number }}</a></li>
                <li class="breadcrumb-item active">{{ __('Edit') }}</li>
            </ol>
        </nav>
        <h1 class="h3">{{ __('Edit Document') }}</h1>
        <p class="text-muted">{{ $document->document_number }} - {{ $document->title }}</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Document Information Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">{{ __('Document Information') }}</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="document_number" class="form-label">{{ __('Document Number') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $document->document_number }}"
                                           disabled>
                                    <small class="text-muted">{{ __('Document number cannot be changed') }}</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="category" class="form-label">{{ __('Document Category') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $document->category_label }}"
                                           disabled>
                                    <small class="text-muted">{{ __('Category cannot be changed') }}</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="title" class="form-label required">{{ __('Document Title') }}</label>
                                    <input type="text"
                                           name="title"
                                           id="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title', $document->title) }}"
                                           required>
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                    <textarea name="description"
                                              id="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="3">{{ old('description', $document->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="owner_id" class="form-label required">{{ __('Document Owner') }}</label>
                                    <select name="owner_id"
                                            id="owner_id"
                                            class="form-select @error('owner_id') is-invalid @enderror"
                                            required>
                                        <option value="">{{ __('Select owner...') }}</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                                {{ old('owner_id', $document->owner_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="version" class="form-label">{{ __('Current Version') }}</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $document->version }} ({{ __('Revision') }} {{ $document->revision_number }})"
                                           disabled>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">{{ __('Document File') }}</h5>

                            <div class="row g-3">
                                @if($document->file_path)
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="icon-base ti tabler-info-circle"></i>
                                        {{ __('Current file:') }} <strong>{{ $document->file_name }}</strong> ({{ $document->file_size_formatted }})
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-12">
                                    <label for="file" class="form-label">{{ __('Replace File (Optional)') }}</label>
                                    <input type="file"
                                           name="file"
                                           id="file"
                                           class="form-control @error('file') is-invalid @enderror"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx">
                                    @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        {{ __('Leave empty to keep current file. Uploading a new file will replace the existing one.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Metadata Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">{{ __('Additional Information') }}</h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="applicable_departments" class="form-label">{{ __('Applicable Departments') }}</label>
                                    <select name="applicable_departments[]"
                                            id="applicable_departments"
                                            class="form-select @error('applicable_departments') is-invalid @enderror"
                                            multiple
                                            size="5">
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}"
                                                {{ in_array($department->id, old('applicable_departments', $document->applicable_departments ?? [])) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('applicable_departments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Hold Ctrl/Cmd to select multiple departments') }}</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="keywords" class="form-label">{{ __('Keywords') }}</label>
                                    <input type="text"
                                           name="keywords"
                                           id="keywords"
                                           class="form-control @error('keywords') is-invalid @enderror"
                                           value="{{ old('keywords', $document->keywords ? implode(', ', $document->keywords) : '') }}">
                                    @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Enter keywords separated by commas') }}</small>
                                </div>

                                @if($document->status !== 'draft')
                                <div class="col-md-12">
                                    <label for="revision_notes" class="form-label">{{ __('Revision Notes') }}</label>
                                    <textarea name="revision_notes"
                                              id="revision_notes"
                                              class="form-control @error('revision_notes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="{{ __('Describe the changes made in this revision...') }}">{{ old('revision_notes') }}</textarea>
                                    @error('revision_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Required for tracking document changes') }}</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>{{ __('Update Document') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-info-circle text-primary"></i> {{ __('Edit Guidelines') }}
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2">{{ __('Only draft and pending review documents can be edited') }}</li>
                        <li class="mb-2">{{ __('Document number and category are fixed after creation') }}</li>
                        <li class="mb-2">{{ __('Version number will auto-increment on save') }}</li>
                        <li class="mb-2">{{ __('Provide revision notes for non-draft documents') }}</li>
                        <li>{{ __('Changes are logged with timestamp and user information') }}</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-alert-triangle text-warning"></i> {{ __('Important') }}
                    </h6>
                    <p class="small mb-0">
                        {{ __('Editing a document will increment its version number.') }}
                        {{ __('For effective documents, a new major version will be created.') }}
                        {{ __('For draft documents, a minor version increment will occur.') }}
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-timeline text-info"></i> {{ __('Current Status') }}
                    </h6>
                    <div class="small">
                        <p class="mb-2">
                            <strong>{{ __('Status:') }}</strong>
                            <span class="badge bg-{{ $document->status_color }}">
                                {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                            </span>
                        </p>
                        <p class="mb-2">
                            <strong>{{ __('Version:') }}</strong> {{ $document->version }}
                        </p>
                        <p class="mb-0">
                            <strong>{{ __('Revision:') }}</strong> {{ $document->revision_number }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Convert keywords to array on form submit
    $('form').on('submit', function() {
        const keywordsInput = $('#keywords');
        if (keywordsInput.val()) {
            const keywords = keywordsInput.val().split(',').map(k => k.trim()).filter(k => k);
            keywordsInput.val(JSON.stringify(keywords));
        }
    });

    // Show file name when selected
    $('#file').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.text-muted').text('{{ __("Selected:") }} ' + fileName);
        }
    });
});
</script>
@endpush
@endsection
