@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation activity logs'))

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header">
      <h4 class="card-title mb-0">{{ __('Valuation activity logs') }}</h4>
    </div>
    <div class="card-body">
      <form method="get" class="row g-1 align-items-end">
        <div class="col-md-4">
          <label class="form-label">{{ __('Valuation request ID') }}</label>
          <input type="number" name="valuation_request_id" class="form-control" value="{{ $valuationRequestId }}">
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
          <a href="{{ route('dashboard.valuation-requests.activity-logs') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('When') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Subject') }}</th>
            <th>{{ __('Description') }}</th>
            <th>{{ __('Event') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
              <td>{{ $log->causer?->name ?? '—' }}</td>
              <td>
                @if ($log->subject_id)
                  <a href="{{ route('dashboard.valuation-requests.show', $log->subject_id) }}">#{{ $log->subject_id }}</a>
                @else
                  —
                @endif
              </td>
              <td>{{ $log->description }}</td>
              <td>{{ data_get($log->properties, 'event') ?? $log->event }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">{{ __('No results') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $logs->links() }}</div>
  </div>
</section>
@endsection
