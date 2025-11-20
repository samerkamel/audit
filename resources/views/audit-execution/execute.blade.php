@extends('layouts/layoutMaster')

@section('title', 'Execute Audit')

@section('content')
<form action="{{ route('audit-execution.store', [$auditPlan, $department, $checklistGroup]) }}" method="POST">
  @csrf

  <div class="row">
    <div class="col-12">
      <!-- Header -->
      <div class="card mb-6">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center mb-2">
                <a href="{{ route('audit-execution.show', $auditPlan) }}" class="btn btn-sm btn-icon btn-label-secondary me-2">
                  <i class="icon-base ti tabler-arrow-left"></i>
                </a>
                <h4 class="mb-0">Execute Audit</h4>
              </div>

              <div class="row g-3 mt-2">
                <div class="col-md-4">
                  <small class="text-muted d-block">Audit Plan</small>
                  <span class="fw-semibold">{{ $auditPlan->title }}</span>
                </div>
                <div class="col-md-4">
                  <small class="text-muted d-block">Department</small>
                  <span class="fw-semibold">{{ $department->name }}</span>
                </div>
                <div class="col-md-4">
                  <small class="text-muted d-block">Checklist Group</small>
                  <span class="fw-semibold">{{ $checklistGroup->code }} - {{ $checklistGroup->title }}</span>
                </div>
              </div>

              @if($checklistGroup->quality_procedure_reference)
                <div class="mt-3">
                  <span class="badge bg-label-info">{{ $checklistGroup->quality_procedure_reference }}</span>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Questions -->
      @foreach($questions as $index => $question)
        @php
          $existingResponse = $existingResponses->get($question->id);
        @endphp

        <div class="card mb-4">
          <div class="card-body">
            <!-- Question Header -->
            <div class="d-flex align-items-start mb-3">
              <div class="avatar avatar-sm me-3">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ $index + 1 }}
                </span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-2">{{ $question->question }}</h6>

                @if($question->iso_reference)
                  <span class="badge bg-label-secondary me-1">{{ $question->iso_reference }}</span>
                @endif
                @if($question->quality_procedure_reference)
                  <span class="badge bg-label-info">{{ $question->quality_procedure_reference }}</span>
                @endif

                @if($question->description)
                  <p class="text-muted mt-2 mb-0"><small>{{ $question->description }}</small></p>
                @endif
              </div>
            </div>

            <!-- Response Options -->
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Response <span class="text-danger">*</span></label>
                <div class="form-check">
                  <input class="form-check-input" type="radio"
                    name="responses[{{ $loop->index }}][response]"
                    id="response_{{ $question->id }}_complied"
                    value="complied"
                    {{ old("responses.{$loop->index}.response", $existingResponse?->response) == 'complied' ? 'checked' : '' }}
                    required>
                  <label class="form-check-label" for="response_{{ $question->id }}_complied">
                    <i class="icon-base ti tabler-check text-success me-1"></i> Complied
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio"
                    name="responses[{{ $loop->index }}][response]"
                    id="response_{{ $question->id }}_not_complied"
                    value="not_complied"
                    {{ old("responses.{$loop->index}.response", $existingResponse?->response) == 'not_complied' ? 'checked' : '' }}
                    required>
                  <label class="form-check-label" for="response_{{ $question->id }}_not_complied">
                    <i class="icon-base ti tabler-x text-danger me-1"></i> Not Complied
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio"
                    name="responses[{{ $loop->index }}][response]"
                    id="response_{{ $question->id }}_not_applicable"
                    value="not_applicable"
                    {{ old("responses.{$loop->index}.response", $existingResponse?->response) == 'not_applicable' ? 'checked' : '' }}
                    required>
                  <label class="form-check-label" for="response_{{ $question->id }}_not_applicable">
                    <i class="icon-base ti tabler-minus text-secondary me-1"></i> Not Applicable
                  </label>
                </div>
                <input type="hidden" name="responses[{{ $loop->index }}][question_id]" value="{{ $question->id }}">
              </div>

              <div class="col-md-6">
                <label for="comments_{{ $question->id }}" class="form-label">Comments / Observations</label>
                <textarea class="form-control" id="comments_{{ $question->id }}"
                  name="responses[{{ $loop->index }}][comments]" rows="4"
                  placeholder="Add any comments, observations, or notes...">{{ old("responses.{$loop->index}.comments", $existingResponse?->comments) }}</textarea>
              </div>
            </div>

            @if($existingResponse)
              <div class="alert alert-info mt-3 mb-0">
                <div class="d-flex align-items-center">
                  <i class="icon-base ti tabler-info-circle me-2"></i>
                  <div>
                    <small>
                      Last audited by <strong>{{ $existingResponse->auditor->name }}</strong>
                      on {{ $existingResponse->audited_at->format('M d, Y H:i') }}
                    </small>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      @endforeach

      @if($questions->count() == 0)
        <div class="card">
          <div class="card-body text-center py-6">
            <div class="avatar avatar-xl mx-auto mb-3">
              <span class="avatar-initial rounded-circle bg-label-warning">
                <i class="icon-base ti tabler-alert-circle" style="font-size: 2rem;"></i>
              </span>
            </div>
            <h5 class="mb-1">No Questions</h5>
            <p class="text-muted mb-0">This checklist group doesn't have any questions assigned yet.</p>
          </div>
        </div>
      @endif

      <!-- Action Buttons -->
      @if($questions->count() > 0)
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <a href="{{ route('audit-execution.show', $auditPlan) }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Responses
              </button>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</form>
@endsection
