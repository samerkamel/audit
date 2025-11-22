@extends('layouts/layoutMaster')

@section('title', 'Create Document')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
                <li class="breadcrumb-item active">Create New Document</li>
            </ol>
        </nav>
        <h1 class="h3">Create Controlled Document</h1>
        <p class="text-muted">Add a new document to the quality management system</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Document Information Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Document Information</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="title" class="form-label required">Document Title</label>
                                    <input type="text"
                                           name="title"
                                           id="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}"
                                           placeholder="e.g., Quality Management Procedure"
                                           required>
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="category" class="form-label required">Document Category</label>
                                    <select name="category"
                                            id="category"
                                            class="form-select @error('category') is-invalid @enderror"
                                            required>
                                        <option value="">Select category...</option>
                                        <option value="quality_manual" {{ old('category') === 'quality_manual' ? 'selected' : '' }}>
                                            Quality Manual
                                        </option>
                                        <option value="procedure" {{ old('category') === 'procedure' ? 'selected' : '' }}>
                                            Procedure
                                        </option>
                                        <option value="work_instruction" {{ old('category') === 'work_instruction' ? 'selected' : '' }}>
                                            Work Instruction
                                        </option>
                                        <option value="form" {{ old('category') === 'form' ? 'selected' : '' }}>
                                            Form
                                        </option>
                                        <option value="record" {{ old('category') === 'record' ? 'selected' : '' }}>
                                            Record
                                        </option>
                                        <option value="external_document" {{ old('category') === 'external_document' ? 'selected' : '' }}>
                                            External Document
                                        </option>
                                    </select>
                                    @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description"
                                              id="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Brief description of the document purpose and scope...">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="owner_id" class="form-label required">Document Owner</label>
                                    <select name="owner_id"
                                            id="owner_id"
                                            class="form-select @error('owner_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select owner...</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="supersedes_id" class="form-label">Supersedes Document</label>
                                    <select name="supersedes_id"
                                            id="supersedes_id"
                                            class="form-select @error('supersedes_id') is-invalid @enderror">
                                        <option value="">None - New document</option>
                                        @foreach($effectiveDocuments as $doc)
                                        <option value="{{ $doc->id }}" {{ old('supersedes_id') == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->document_number }} - {{ $doc->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('supersedes_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select if this document replaces an existing one</small>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Document File</h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="file" class="form-label">Upload File</label>
                                    <input type="file"
                                           name="file"
                                           id="file"
                                           class="form-control @error('file') is-invalid @enderror"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx">
                                    @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Accepted formats: PDF, DOC, DOCX, XLS, XLSX (Max: 10MB)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Metadata Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Additional Information</h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="applicable_departments" class="form-label">Applicable Departments</label>
                                    <select name="applicable_departments[]"
                                            id="applicable_departments"
                                            class="form-select @error('applicable_departments') is-invalid @enderror"
                                            multiple
                                            size="5">
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}"
                                                {{ in_array($department->id, old('applicable_departments', [])) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('applicable_departments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple departments</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="keywords" class="form-label">Keywords</label>
                                    <input type="text"
                                           name="keywords"
                                           id="keywords"
                                           class="form-control @error('keywords') is-invalid @enderror"
                                           value="{{ old('keywords') }}"
                                           placeholder="quality, procedure, ISO 9001 (comma-separated)">
                                    @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter keywords separated by commas for easier searching</small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-file-plus me-1"></i>Create Document
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
                        <i class="icon-base ti tabler-info-circle text-primary"></i> Document Control Guidelines
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2">Select the appropriate category for proper numbering</li>
                        <li class="mb-2">Document number will be auto-generated upon creation</li>
                        <li class="mb-2">All documents start in "Draft" status</li>
                        <li class="mb-2">Upload the latest version of your document file</li>
                        <li>Keywords help with searching and categorization</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-bookmark text-info"></i> Document Categories
                    </h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong>Quality Manual (QM):</strong> High-level quality policy documents
                        </div>
                        <div class="mb-2">
                            <strong>Procedure (PROC):</strong> Step-by-step process instructions
                        </div>
                        <div class="mb-2">
                            <strong>Work Instruction (WI):</strong> Detailed task-level instructions
                        </div>
                        <div class="mb-2">
                            <strong>Form (FORM):</strong> Template documents for data collection
                        </div>
                        <div class="mb-2">
                            <strong>Record (REC):</strong> Completed forms and evidence
                        </div>
                        <div>
                            <strong>External (EXT):</strong> Third-party or regulatory documents
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-route text-warning"></i> Document Workflow
                    </h6>
                    <p class="small mb-0">
                        After creation, documents follow this workflow:
                    </p>
                    <ol class="small mb-0 ps-3">
                        <li>Draft → Submit for Review</li>
                        <li>Pending Review → Review & Approve</li>
                        <li>Pending Approval → Approve</li>
                        <li>Approved → Make Effective</li>
                        <li>Effective → (Annual Review)</li>
                    </ol>
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
            $(this).next('.text-muted').text('Selected: ' + fileName);
        }
    });
});
</script>
@endpush
@endsection
