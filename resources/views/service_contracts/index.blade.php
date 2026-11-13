@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation service contracts'))

@section('content')
<section id="service-contracts">
  @include('valuation_requests.partials.flash')
  @if (session('download'))
    <div class="alert alert-info p-1">
      <a href="{{ session('download') }}" target="_blank" rel="noopener">{{ __('Preview the contract') }}</a>
      · <a href="{{ session('download') }}?download=1">{{ __('Download') }}</a>
    </div>
  @endif
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="card-title mb-0">{{ __('Valuation service contracts') }}</h4>
      <a href="{{ route('dashboard.service-contracts.create') }}" class="btn btn-sm btn-primary">{{ __('New contract') }}</a>
    </div>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr><th>{{ __('Contract number') }}</th><th>{{ __('Contract date') }}</th><th>{{ __('Client name') }}</th><th>{{ __('City') }}</th><th>{{ __('Neighborhood') }}</th><th>{{ __('Contract value (SAR)') }}</th><th></th></tr>
        </thead>
        <tbody>
          @forelse ($contracts as $contract)
            <tr>
              <td>{{ $contract->number }}</td>
              <td>{{ $contract->contract_date->format('Y/m/d') }}</td>
              <td>{{ $contract->client_name }}</td>
              <td>{{ $contract->city?->name_ar }}</td>
              <td>{{ $contract->neighborhood?->name_ar }}</td>
              <td>{{ number_format((float) $contract->contract_value, 2) }}</td>
              <td class="text-nowrap">
                <a href="{{ route('dashboard.service-contracts.pdf', $contract) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">{{ __('Contract') }}</a>
                <a href="{{ route('dashboard.service-contracts.pdf', [$contract, 'download' => 1]) }}" class="btn btn-sm btn-outline-secondary">{{ __('Download') }}</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">{{ __('No contracts yet.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($contracts->hasPages())
      <div class="card-footer">{{ $contracts->links() }}</div>
    @endif
  </div>
</section>
@endsection
