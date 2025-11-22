@extends('layouts/layoutMaster')

@section('title', __('Audit Plans Management'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Audit Plans Management') }}</h4>
      <p class="text-muted mb-0">{{ __('Plan and schedule organizational audits') }}</p>
    </div>
    <a href="{{ route('audit-plans.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-plus me-1"></i> {{ __('Create Audit Plan') }}
    </a>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Total') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-clipboard-check icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Draft') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['draft'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base ti tabler-file-text icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Planned') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['planned'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-info">
                <i class="icon-base ti tabler-calendar icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('In Progress') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['in_progress'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-progress icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Completed') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['completed'] }}</h4>
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
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">{{ __('Overdue') }}</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['overdue'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-danger">
                <i class="icon-base ti tabler-alert-circle icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- View Toggle Buttons -->
  <div class="d-flex justify-content-end mb-3">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-outline-primary active" id="tableViewBtn" onclick="toggleView('table')">
        <i class="icon-base ti tabler-table me-1"></i> {{ __('Table View') }}
      </button>
      <button type="button" class="btn btn-outline-primary" id="ganttViewBtn" onclick="toggleView('gantt')">
        <i class="icon-base ti tabler-chart-gantt me-1"></i> {{ __('Gantt Chart') }}
      </button>
    </div>
  </div>

  <!-- Gantt Chart Card -->
  <div class="card mb-6" id="ganttChartCard" style="display: none;">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('Audit Plans Timeline') }} ({{ date('Y') }})</h5>
      <div class="d-flex gap-2">
        <span class="badge bg-info"><i class="icon-base ti tabler-circle-filled me-1"></i>{{ __('Planned') }}</span>
        <span class="badge bg-warning"><i class="icon-base ti tabler-circle-filled me-1"></i>{{ __('In Progress') }}</span>
        <span class="badge bg-success"><i class="icon-base ti tabler-circle-filled me-1"></i>{{ __('Completed') }}</span>
        <span class="badge bg-danger"><i class="icon-base ti tabler-circle-filled me-1"></i>{{ __('Overdue') }}</span>
      </div>
    </div>
    <div class="card-body">
      <div id="ganttChart"></div>
    </div>
  </div>

  <!-- Audit Plans List Card -->
  <div class="card" id="tableViewCard">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ __('Audit Plans List') }}</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-audit-plans table">
        <thead>
          <tr>
            <th>{{ __('Title') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Sector') }}</th>
            <th>{{ __('Lead Auditor') }}</th>
            <th>{{ __('Planned Dates') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($auditPlans as $plan)
          <tr>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $plan->title }}</span>
                @if($plan->departments->count() > 0)
                <small class="text-muted">
                  {{ $plan->departments->pluck('name')->implode(', ') }}
                  @if($plan->departments->count() > 3)
                    <span class="badge bg-label-secondary ms-1">+{{ $plan->departments->count() - 3 }} more</span>
                  @endif
                </small>
                @endif
              </div>
            </td>
            <td>
              <span class="badge bg-label-info">{{ $plan->audit_type_label }}</span>
            </td>
            <td>{{ $plan->sector?->name ?? '-' }}</td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $plan->leadAuditor?->name ?? 'Not Assigned' }}</span>
                <small class="text-muted">{{ $plan->leadAuditor?->email ?? '' }}</small>
              </div>
            </td>
            <td>
              <div class="d-flex flex-column">
                <small>{{ $plan->planned_start_date?->format('d M Y') ?? '-' }}</small>
                <small>{{ $plan->planned_end_date?->format('d M Y') ?? '-' }}</small>
              </div>
            </td>
            <td>
              <span class="badge bg-label-{{ $plan->status_color }}">{{ ucfirst(str_replace('_', ' ', $plan->status)) }}</span>
              @if($plan->isOverdue())
              <span class="badge bg-label-danger ms-1">Overdue</span>
              @endif
            </td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('audit-plans.show', $plan) }}">
                    <i class="icon-base ti tabler-eye me-1"></i> View
                  </a>
                  <a class="dropdown-item" href="{{ route('audit-plans.edit', $plan) }}">
                    <i class="icon-base ti tabler-edit me-1"></i> Edit
                  </a>
                  @if($plan->status === 'planned')
                  <form action="{{ route('audit-plans.start', $plan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-player-play me-1"></i> Start
                    </button>
                  </form>
                  @endif
                  @if($plan->status === 'in_progress')
                  <form action="{{ route('audit-plans.complete', $plan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-circle-check me-1"></i> Complete
                    </button>
                  </form>
                  @endif
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('audit-plans.destroy', $plan) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this audit plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@if(session('success'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: '{{ session('success') }}',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });
</script>
@endif

@if(session('error'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: '{{ session('error') }}',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });
</script>
@endif
@endsection

@section('page-script')
<script>
  // Gantt chart data (prepared in controller)
  const ganttData = @json($ganttData);

  let ganttChart = null;

  function initGanttChart() {
    if (ganttChart) {
      ganttChart.destroy();
    }

    if (ganttData.length === 0) {
      document.getElementById('ganttChart').innerHTML = '<div class="text-center py-5"><p class="text-muted">No audit plans with scheduled dates to display</p></div>';
      return;
    }

    const options = {
      series: [{
        data: ganttData
      }],
      chart: {
        height: Math.max(300, ganttData.length * 50),
        type: 'rangeBar',
        toolbar: {
          show: true,
          tools: {
            download: true,
            selection: false,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: true,
            reset: true
          }
        },
        events: {
          dataPointSelection: function(event, chartContext, config) {
            const planId = ganttData[config.dataPointIndex].id;
            window.location.href = '/audit-plans/' + planId;
          }
        }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          distributed: true,
          barHeight: '70%',
          borderRadius: 4
        }
      },
      dataLabels: {
        enabled: true,
        formatter: function(val, opts) {
          const start = new Date(val[0]);
          const end = new Date(val[1]);
          const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
          return diff + ' days';
        },
        style: {
          fontSize: '11px',
          fontWeight: 500,
          colors: ['#fff']
        }
      },
      xaxis: {
        type: 'datetime',
        labels: {
          datetimeFormatter: {
            year: 'yyyy',
            month: 'MMM',
            day: 'dd MMM',
            hour: 'HH:mm'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            fontSize: '12px'
          },
          maxWidth: 200
        }
      },
      grid: {
        row: {
          colors: ['#f3f4f6', 'transparent'],
          opacity: 0.5
        }
      },
      tooltip: {
        custom: function({ series, seriesIndex, dataPointIndex, w }) {
          const data = ganttData[dataPointIndex];
          const start = new Date(data.y[0]);
          const end = new Date(data.y[1]);
          const options = { year: 'numeric', month: 'short', day: 'numeric' };

          return '<div class="p-3">' +
            '<strong>' + data.x + '</strong><br>' +
            '<span class="text-muted">Start:</span> ' + start.toLocaleDateString('en-US', options) + '<br>' +
            '<span class="text-muted">End:</span> ' + end.toLocaleDateString('en-US', options) + '<br>' +
            '<span class="text-muted">Status:</span> ' + data.status.replace('_', ' ') +
            '</div>';
        }
      },
      legend: {
        show: false
      }
    };

    ganttChart = new ApexCharts(document.querySelector("#ganttChart"), options);
    ganttChart.render();
  }

  function toggleView(view) {
    const tableCard = document.getElementById('tableViewCard');
    const ganttCard = document.getElementById('ganttChartCard');
    const tableBtn = document.getElementById('tableViewBtn');
    const ganttBtn = document.getElementById('ganttViewBtn');

    if (view === 'table') {
      tableCard.style.display = 'block';
      ganttCard.style.display = 'none';
      tableBtn.classList.add('active');
      ganttBtn.classList.remove('active');
    } else {
      tableCard.style.display = 'none';
      ganttCard.style.display = 'block';
      tableBtn.classList.remove('active');
      ganttBtn.classList.add('active');

      // Initialize chart when showing Gantt view
      setTimeout(function() {
        initGanttChart();
      }, 100);
    }

    // Save preference
    localStorage.setItem('auditPlansView', view);
  }

  $(document).ready(function() {
    $('.datatables-audit-plans').DataTable({
      responsive: true,
      order: [[0, 'desc']],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { search: '', searchPlaceholder: 'Search audit plans...' }
    });

    // Restore saved view preference
    const savedView = localStorage.getItem('auditPlansView');
    if (savedView === 'gantt') {
      toggleView('gantt');
    }
  });
</script>
@endsection
