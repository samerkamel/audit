<div class="row">
  <!-- Name -->
  <div class="col-md-6 mb-4">
    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
      value="{{ old('name', $user->name ?? '') }}" placeholder="Enter full name" required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Email -->
  <div class="col-md-6 mb-4">
    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
      value="{{ old('email', $user->email ?? '') }}" placeholder="user@alfa-electronics.com" required>
    @error('email')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Password -->
  <div class="col-md-6 mb-4">
    <label for="password" class="form-label">
      Password
      @if(isset($user))
        <span class="text-muted">(leave blank to keep current)</span>
      @else
        <span class="text-danger">*</span>
      @endif
    </label>
    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
      placeholder="Enter password" {{ isset($user) ? '' : 'required' }}>
    @error('password')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Password Confirmation -->
  <div class="col-md-6 mb-4">
    <label for="password_confirmation" class="form-label">
      Confirm Password
      @if(isset($user))
        <span class="text-muted">(leave blank to keep current)</span>
      @else
        <span class="text-danger">*</span>
      @endif
    </label>
    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
      placeholder="Confirm password" {{ isset($user) ? '' : 'required' }}>
  </div>

  <!-- Phone -->
  <div class="col-md-6 mb-4">
    <label for="phone" class="form-label">Phone</label>
    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
      value="{{ old('phone', $user->phone ?? '') }}" placeholder="+966-11-1234567">
    @error('phone')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Mobile -->
  <div class="col-md-6 mb-4">
    <label for="mobile" class="form-label">Mobile</label>
    <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile"
      value="{{ old('mobile', $user->mobile ?? '') }}" placeholder="+966-50-1234567">
    @error('mobile')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Sector -->
  <div class="col-md-6 mb-4">
    <label for="sector_id" class="form-label">Sector</label>
    <select class="form-select select2 @error('sector_id') is-invalid @enderror" id="sector_id" name="sector_id">
      <option value="">Select Sector</option>
      @foreach($sectors as $sector)
        <option value="{{ $sector->id }}" {{ old('sector_id', $user->sector_id ?? '') == $sector->id ? 'selected' : '' }}>
          {{ $sector->name }}
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
      <option value="">Select Department</option>
      @foreach($departments as $department)
        <option value="{{ $department->id }}" {{ old('department_id', $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
          {{ $department->name }}
        </option>
      @endforeach
    </select>
    @error('department_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Language -->
  <div class="col-md-6 mb-4">
    <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
      <option value="en" {{ old('language', $user->language ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
      <option value="ar" {{ old('language', $user->language ?? 'en') == 'ar' ? 'selected' : '' }}>Arabic</option>
    </select>
    @error('language')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Status -->
  <div class="col-md-6 mb-4">
    <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
      <option value="1" {{ old('is_active', $user->is_active ?? '1') == '1' ? 'selected' : '' }}>Active</option>
      <option value="0" {{ old('is_active', $user->is_active ?? '1') == '0' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('is_active')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Roles -->
  <div class="col-12 mb-4">
    <label class="form-label">Roles <span class="text-danger">*</span></label>
    @error('roles')
      <div class="text-danger">{{ $message }}</div>
    @enderror
    <div class="row">
      @foreach($roles as $role)
        <div class="col-md-4 mb-2">
          <div class="form-check custom-option custom-option-basic">
            <label class="form-check-label custom-option-content" for="role_{{ $role->id }}">
              <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                id="role_{{ $role->id }}"
                {{ (isset($user) && $user->roles->pluck('name')->contains($role->name)) || (is_array(old('roles')) && in_array($role->name, old('roles'))) ? 'checked' : '' }}>
              <span class="custom-option-header">
                <span class="h6 mb-0">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
              </span>
              <span class="custom-option-body">
                <small class="text-muted">{{ $role->description ?? 'Role permissions' }}</small>
              </span>
            </label>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<!-- Submit Button -->
<div class="pt-4">
  <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ $submitLabel }}</button>
  <a href="{{ route('users.index') }}" class="btn btn-label-secondary">Cancel</a>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
      theme: 'bootstrap-5',
      placeholder: 'Select an option',
      allowClear: true
    });

    // Filter departments by sector
    $('#sector_id').on('change', function() {
      const sectorId = $(this).val();
      const $departmentSelect = $('#department_id');

      if (sectorId) {
        // Filter departments by selected sector
        $departmentSelect.find('option').each(function() {
          const $option = $(this);
          if ($option.val() === '') {
            $option.show();
            return;
          }

          // In a real implementation, you'd check department's sector_id
          // For now, show all departments
          $option.show();
        });
      } else {
        $departmentSelect.find('option[value!=""]').show();
      }

      $departmentSelect.val('').trigger('change');
    });
  });
</script>
@endpush
