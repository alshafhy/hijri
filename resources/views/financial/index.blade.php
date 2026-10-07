@extends('layouts/contentLayoutMaster')

@section('title', __('Financial board'))

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Financial board') }}</h4>
      <form method="GET" class="d-flex gap-1 align-items-center">
        <label class="form-label mb-0">{{ __('Month') }}</label>
        <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm">
        <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Filter') }}</button>
      </form>
    </div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-2"><strong>{{ __('Requests count') }}:</strong> {{ $summary['requests_count'] }}</div>
        <div class="col-md-2"><strong>{{ __('Coordinator share') }}:</strong> {{ number_format((float) $summary['coordinator_share_total'], 2) }}</div>
        <div class="col-md-2"><strong>{{ __('Evaluator share') }}:</strong> {{ number_format((float) $summary['evaluator_share_total'], 2) }}</div>
        <div class="col-md-2"><strong>{{ __('Manager share') }}:</strong> {{ number_format((float) $summary['manager_share_total'], 2) }}</div>
        <div class="col-md-2"><strong>{{ __('Contractor fees paid') }}:</strong> {{ number_format((float) $summary['contractor_fees_paid'], 2) }}</div>
        <div class="col-md-2"><strong>{{ __('Contractor fees unpaid') }}:</strong> {{ number_format((float) $summary['contractor_fees_unpaid'], 2) }}</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('Coordinator') }}</th>
            <th>{{ __('Evaluator') }}</th>
            <th>{{ __('Coordinator share') }}</th>
            <th>{{ __('Evaluator share') }}</th>
            <th>{{ __('Manager share') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rows as $request)
            @php $shares = $request->feeShares->first(); @endphp
            <tr>
              <td>{{ $request->id }}</td>
              <td>{{ $request->number }}</td>
              <td>{{ $request->coordinator?->name }}</td>
              <td>{{ $request->evaluator?->name }}</td>
              <td>{{ number_format((float) ($shares?->coordinator_share ?? 0), 2) }}</td>
              <td>{{ number_format((float) ($shares?->evaluator_share ?? 0), 2) }}</td>
              <td>{{ number_format((float) ($shares?->manager_share ?? 0), 2) }}</td>
              <td>
                <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $rows->links() }}</div>
  </div>
</section>
@endsection
