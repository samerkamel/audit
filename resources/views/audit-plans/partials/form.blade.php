<div class="row">
  <!-- Title -->
  <div class="col-md-12 mb-4">
    <label for="title" class="form-label">Audit Plan Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" 
      id="title" name="title" value="{{ old('title', $auditPlan->title ?? '') }}" 
      placeholder="Annual IT Security Audit 2025" required>
    @error('title')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Audit Type -->
  <div class="col-md-6 mb-4">
    <label for="audit_type" class="form-label">Audit Type <span class="text-danger">*</span></label>
    <select class="form-select @error('audit_type') is-invalid @enderror" id="audit_type" name="audit_type" required>
      <option value="">Select Audit Type</option>
      <option value="internal" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'internal' ? 'selected' : '' }}>Internal Audit</option>
      <option value="external" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'external' ? 'selected' : '' }}>External Audit</option>
      <option value="compliance" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'compliance' ? 'selected' : '' }}>Compliance Audit</option>
      <option value="operational" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'operational' ? 'selected' : '' }}>Operational Audit</option>
      <option value="financial" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'financial' ? 'selected' : '' }}>Financial Audit</option>
      <option value="it" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'it' ? 'selected' : '' }}>IT Audit</option>
      <option value="quality" {{ old('audit_type', $auditPlan->audit_type ?? '') == 'quality' ? 'selected' : '' }}>Quality Audit</option>
    </select>
    @error('audit_type')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Status -->
  <div class="col-md-6 mb-4">
    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
      <option value="draft" {{ old('status', $auditPlan->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
      <option value="planned" {{ old('status', $auditPlan->status ?? '') == 'planned' ? 'selected' : '' }}>Planned</option>
      <option value="in_progress" {{ old('status', $auditPlan->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
      <option value="completed" {{ old('status', $auditPlan->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="cancelled" {{ old('status', $auditPlan->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    @error('status')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Sector -->
  <div class="col-md-6 mb-4">
    <label for="sector_id" class="form-label">Sector <span class="text-danger">*</span></label>
    <select class="form-select select2 @error('sector_id') is-invalid @enderror" id="sector_id" name="sector_id" required>
      <option value="">Select Sector</option>
      @foreach($sectors as $sector)
        <option value="{{ $sector->id }}" {{ old('sector_id', $auditPlan->sector_id ?? '') == $sector->id ? 'selected' : '' }}>
          {{ $sector->name }} ({{ $sector->code }})
        </option>
      @endforeach
    </select>
    @error('sector_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Department -->
  <div class="col-md-6 mb-4">
    <label for="department_id" class="form-label">Department</label>
    <select class="form-select select2 @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
      <option value="">Select Department (Optional)</option>
      @foreach($departments as $department)
        <option value="{{ $department->id }}" {{ old('department_id', $auditPlan->department_id ?? '') == $department->id ? 'selected' : '' }}>
          {{ $department->name }} ({{ $department->code }})
        </option>
      @endforeach
    </select>
    @error('department_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Lead Auditor -->
  <div class="col-md-12 mb-4">
    <label for="lead_auditor_id" class="form-label">Lead Auditor <span class="text-danger">*</span></label>
    <select class="form-select select2 @error('lead_auditor_id') is-invalid @enderror" id="lead_auditor_id" name="lead_auditor_id" required>
      <option value="">Select Lead Auditor</option>
      @foreach($auditors as $auditor)
        <option value="{{ $auditor->id }}" {{ old('lead_auditor_id', $auditPlan->lead_auditor_id ?? '') == $auditor->id ? 'selected' : '' }}>
          {{ $auditor->name }} ({{ $auditor->email }})
        </option>
      @endforeach
    </select>
    @error('lead_auditor_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Planned Start Date -->
  <div class="col-md-6 mb-4">
    <label for="planned_start_date" class="form-label">Planned Start Date <span class="text-danger">*</span></label>
    <input type="date" class="form-control @error('planned_start_date') is-invalid @enderror" 
      id="planned_start_date" name="planned_start_date" 
      value="{{ old('planned_start_date', isset($auditPlan) ? $auditPlan->planned_start_date->format('Y-m-d') : '') }}" 
      required>
    @error('planned_start_date')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Planned End Date -->
  <div class="col-md-6 mb-4">
    <label for="planned_end_date" class="form-label">Planned End Date <span class="text-danger">*</span></label>
    <input type="date" class="form-control @error('planned_end_date') is-invalid @enderror" 
      id="planned_end_date" name="planned_end_date" 
      value="{{ old('planned_end_date', isset($auditPlan) ? $auditPlan->planned_end_date->format('Y-m-d') : '') }}" 
      required>
    @error('planned_end_date')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  @if(isset($auditPlan) && $auditPlan->exists)
  <!-- Actual Start Date -->
  <div class="col-md-6 mb-4">
    <label for="actual_start_date" class="form-label">Actual Start Date</label>
    <input type="date" class="form-control @error('actual_start_date') is-invalid @enderror" 
      id="actual_start_date" name="actual_start_date" 
      value="{{ old('actual_start_date', $auditPlan->actual_start_date ? $auditPlan->actual_start_date->format('Y-m-d') : '') }}">
    @error('actual_start_date')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Actual End Date -->
  <div class="col-md-6 mb-4">
    <label for="actual_end_date" class="form-label">Actual End Date</label>
    <input type="date" class="form-control @error('actual_end_date') is-invalid @enderror" 
      id="actual_end_date" name="actual_end_date" 
      value="{{ old('actual_end_date', $auditPlan->actual_end_date ? $auditPlan->actual_end_date->format('Y-m-d') : '') }}">
    @error('actual_end_date')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  @endif

  <!-- Description -->
  <div class="col-md-12 mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" 
      id="description" name="description" rows="3" 
      placeholder="Brief description of the audit plan">{{ old('description', $auditPlan->description ?? '') }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Scope -->
  <div class="col-md-12 mb-4">
    <label for="scope" class="form-label">Audit Scope</label>
    <textarea class="form-control @error('scope') is-invalid @enderror" 
      id="scope" name="scope" rows="3" 
      placeholder="Define the boundaries and extent of the audit">{{ old('scope', $auditPlan->scope ?? '') }}</textarea>
    @error('scope')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Objectives -->
  <div class="col-md-12 mb-4">
    <label for="objectives" class="form-label">Audit Objectives</label>
    <textarea class="form-control @error('objectives') is-invalid @enderror" 
      id="objectives" name="objectives" rows="3" 
      placeholder="List the key objectives and goals of this audit">{{ old('objectives', $auditPlan->objectives ?? '') }}</textarea>
    @error('objectives')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Active Status -->
  <div class="col-md-12 mb-4">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
        {{ old('is_active', $auditPlan->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>
  </div>
</div>
