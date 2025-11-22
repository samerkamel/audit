@extends('layouts/layoutMaster')

@section('title', __('CAR Management'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Corrective Action Requests (CAR)') }}</h4>
      <p class="text-muted mb-0">{{ __('Manage and track corrective actions from non-conformances') }}</p>
    </div>
    <div class="d-flex gap-2">
      <!-- Export Dropdown -->
      <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="icon-base ti tabler-download me-1"></i> {{ __('Export') }}
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="{{ route('reports.cars.pdf') }}" target="_blank">
              <i class="icon-base ti tabler-file-type-pdf me-2 text-danger"></i>{{ __('Export to PDF') }}
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('reports.cars.excel') }}">
              <i class="icon-base ti tabler-file-spreadsheet me-2 text-success"></i>{{ __('Export to Excel') }}
            </a>
          </li>
        </ul>
      </div>
      <form action="{{ route('cars.auto-create-from-findings') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-label-primary" onclick="return confirm('{{ __('Create CARs from all non-compliant findings without existing CARs?') }}')">
          <i class="icon-base ti tabler-wand me-1"></i> {{ __('Auto-Create CARs') }}
        </button>
      </form>
      <a href="{{ route('cars.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> {{ __('Create CAR') }}
      </a>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Total CARs') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['total'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-alert-circle icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Issued') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['issued'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-info">
                <i class="icon-base ti tabler-send icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('In Progress') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['in_progress'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-warning">
                <i class="icon-base ti tabler-progress icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Closed') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['closed'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-success">
                <i class="icon-base ti tabler-circle-check icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Priority & Status Row -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Critical Priority') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['critical_priority'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-danger">
                <i class="icon-base ti tabler-alert-triangle icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Overdue') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['overdue'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-danger">
                <i class="icon-base ti tabler-clock-exclamation icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Pending Approval') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['pending_approval'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base ti tabler-hourglass icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Late') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['late'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-danger">
                <i class="icon-base ti tabler-clock-x icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CAR List -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ __('CARs List') }}</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-cars table dt-responsive nowrap" style="width:100%">
        <thead>
          <tr>
            <th>{{ __('CAR Number') }}</th>
            <th>{{ __('Subject') }}</th>
            <th>{{ __('From') }}</th>
            <th>{{ __('To Department') }}</th>
            <th>{{ __('Source Type') }}</th>
            <th>{{ __('Priority') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Issued Date') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cars as $car)
          <tr>
            <td>
              <a href="{{ route('cars.show', $car) }}" class="fw-semibold text-primary">
                {{ $car->car_number }}
              </a>
            </td>
            <td>
              <div class="text-truncate" style="max-width: 200px;" title="{{ $car->subject }}">
                {{ $car->subject }}
              </div>
            </td>
            <td>{{ $car->fromDepartment->name }}</td>
            <td>{{ $car->toDepartment->name }}</td>
            <td>
              <span class="badge bg-label-secondary">
                {{ ucwords(str_replace('_', ' ', $car->source_type)) }}
              </span>
            </td>
            <td>
              <span class="badge bg-label-{{ $car->priority_color }}">
                {{ ucfirst($car->priority) }}
              </span>
            </td>
            <td>
              <span class="badge bg-label-{{ $car->status_color }}">
                {{ ucwords(str_replace('_', ' ', $car->status)) }}
              </span>
            </td>
            <td>{{ $car->issued_date->format('M d, Y') }}</td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('cars.show', $car) }}">
                    <i class="icon-base ti tabler-eye me-1"></i> {{ __('View Details') }}
                  </a>
                  @if(in_array($car->status, ['draft', 'rejected_to_be_edited']))
                  <a class="dropdown-item" href="{{ route('cars.edit', $car) }}">
                    <i class="icon-base ti tabler-edit me-1"></i> {{ __('Edit') }}
                  </a>
                  @endif
                  @if($car->status === 'draft')
                  <form action="{{ route('cars.submit-for-approval', $car) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-send me-1"></i> {{ __('Submit for Approval') }}
                    </button>
                  </form>
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('cars.destroy', $car) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this CAR?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="icon-base ti tabler-trash me-1"></i> {{ __('Delete') }}
                    </button>
                  </form>
                  @endif
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9" class="text-center py-4">
              <i class="icon-base ti tabler-database-off icon-48px text-muted mb-2"></i>
              <p class="text-muted mb-0">{{ __('No CARs found') }}</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
$(document).ready(function() {
  $('.datatables-cars').DataTable({
    responsive: true,
    order: [[7, 'desc']], // Order by issued date descending
    pageLength: 15,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    columnDefs: [
      { responsivePriority: 1, targets: 0 }, // CAR Number always visible
      { responsivePriority: 2, targets: -1 } // Actions always visible
    ],
    language: {
      search: '',
      searchPlaceholder: '{{ __('Search CARs...') }}'
    }
  });
});
</script>
@endsection
