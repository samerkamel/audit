<div class="row">
  <div class="col-md-6 mb-4">
    <label for="sector_id" class="form-label">Sector <span class="text-danger">*</span></label>
    <select class="form-select select2 @error('sector_id') is-invalid @enderror" id="sector_id" name="sector_id" required>
      <option value="">Select Sector</option>
      @foreach($sectors as $sector)
        <option value="{{ $sector->id }}" {{ old('sector_id', $department->sector_id ?? '') == $sector->id ? 'selected' : '' }}>
          {{ $sector->name }} ({{ $sector->code }})
        </option>
      @endforeach
    </select>
    @error('sector_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="code" class="form-label">Department Code <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
      value="{{ old('code', $department->code ?? '') }}" placeholder="DEPT-001" required>
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="name" class="form-label">Name (English) <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
      value="{{ old('name', $department->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="name_ar" class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar"
      value="{{ old('name_ar', $department->name_ar ?? '') }}" dir="rtl" required>
    @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="manager_id" class="form-label">Department Manager</label>
    <select class="form-select select2 @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
      <option value="">Select Manager</option>
      @foreach($managers as $manager)
        <option value="{{ $manager->id }}" {{ old('manager_id', $department->manager_id ?? '') == $manager->id ? 'selected' : '' }}>
          {{ $manager->name }}
        </option>
      @endforeach
    </select>
    @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="general_manager_id" class="form-label">General Manager</label>
    <select class="form-select select2 @error('general_manager_id') is-invalid @enderror" id="general_manager_id" name="general_manager_id">
      <option value="">Select General Manager</option>
      @foreach($managers as $manager)
        <option value="{{ $manager->id }}" {{ old('general_manager_id', $department->general_manager_id ?? '') == $manager->id ? 'selected' : '' }}>
          {{ $manager->name }}
        </option>
      @endforeach
    </select>
    @error('general_manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="form-text text-muted">General Manager will be CC'd on notifications</small>
  </div>

  <div class="col-md-6 mb-4">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
      value="{{ old('email', $department->email ?? '') }}">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="phone" class="form-label">Phone</label>
    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
      value="{{ old('phone', $department->phone ?? '') }}">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-4">
    <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
      <option value="1" {{ old('is_active', $department->is_active ?? '1') == '1' ? 'selected' : '' }}>Active</option>
      <option value="0" {{ old('is_active', $department->is_active ?? '1') == '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-12 mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
      rows="3">{{ old('description', $department->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>

<div class="pt-4">
  <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ $submitLabel }}</button>
  <a href="{{ route('departments.index') }}" class="btn btn-label-secondary">Cancel</a>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap-5', placeholder: 'Select option', allowClear: true });
  });
</script>
@endpush
