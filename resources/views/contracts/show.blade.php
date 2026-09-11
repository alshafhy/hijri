@extends('layouts/contentLayoutMaster')

@section('title', __('Contract') . ' #' . $contract->id)

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Contract') }} #{{ $contract->id }}</h4>
      <div class="d-flex gap-1 flex-wrap">
        @can('markPaid', $contract)
          @unless ($contract->isPaid())
            <form method="POST" action="{{ route('dashboard.contracts.mark-paid', $contract) }}">@csrf
              <button class="btn btn-sm btn-success" type="submit">{{ __('Mark as paid') }}</button>
            </form>
          @endunless
        @endcan
        <a href="{{ route('dashboard.contracts.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Back') }}</a>
      </div>
    </div>
    <div class="card-body row">
      <div class="col-md-4"><strong>{{ __('Contractor') }}:</strong> {{ $contract->contractor?->name }}</div>
      <div class="col-md-4"><strong>{{ __('Valuation request') }}:</strong> {{ $contract->valuationRequest?->number }}</div>
      <div class="col-md-4"><strong>{{ __('Fees') }}:</strong> {{ $contract->contractor?->fees }}</div>
      <div class="col-md-4 mt-1">
        <strong>{{ __('State') }}:</strong>
        @if ($contract->isPaid())
          <span class="badge bg-success">{{ __('Paid') }}</span>
        @else
          <span class="badge bg-warning">{{ __('Unpaid') }}</span>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
