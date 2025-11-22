<div class="row">
  <!-- Sector Code -->
  <div class="col-md-6 mb-4">
    <label for="code" class="form-label">Sector Code <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
      value="{{ old('code', $sector->code ?? '') }}" placeholder="SEC-001" required>
    <div class="form-text">Unique identifier for the sector (e.g., SEC-001, IT, FIN)</div>
    @error('code')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Sector Name (English) -->
  <div class="col-md-6 mb-4">
    <label for="name" class="form-label">Sector Name (English) <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
      value="{{ old('name', $sector->name ?? '') }}" placeholder="Information Technology" required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Sector Name (Arabic) -->
  <div class="col-md-6 mb-4">
    <label for="name_ar" class="form-label">Sector Name (Arabic) <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar"
      value="{{ old('name_ar', $sector->name_ar ?? '') }}" placeholder="تكنولوجيا المعلومات" dir="rtl" required>
    @error('name_ar')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Director -->
  <div class="col-md-6 mb-4">
    <label for="director_id" class="form-label">Sector Director</label>
    <select class="form-select select2 @error('director_id') is-invalid @enderror" id="director_id" name="director_id">
      <option value="">Select Director</option>
      @foreach($directors as $director)
        <option value="{{ $director->id }}"
          {{ old('director_id', $sector->director_id ?? '') == $director->id ? 'selected' : '' }}>
          {{ $director->name }} - {{ $director->email }}
        </option>
      @endforeach
    </select>
    <div class="form-text">Assign a director to manage this sector</div>
    @error('director_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Description -->
  <div class="col-12 mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
      rows="3" placeholder="Brief description of the sector's purpose and responsibilities">{{ old('description', $sector->description ?? '') }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Status -->
  <div class="col-md-6 mb-4">
    <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
      <option value="1" {{ old('is_active', $sector->is_active ?? '1') == '1' ? 'selected' : '' }}>Active</option>
      <option value="0" {{ old('is_active', $sector->is_active ?? '1') == '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('is_active')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>

<!-- Submit Button -->
<div class="pt-4">
  <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ $submitLabel }}</button>
  <a href="{{ route('sectors.index') }}" class="btn btn-label-secondary">Cancel</a>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
      theme: 'bootstrap-5',
      placeholder: 'Select director',
      allowClear: true
    });
  });
</script>
@endpush
