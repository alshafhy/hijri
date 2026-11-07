@extends('layouts/contentLayoutMaster')

@section('title', __('Preparing report'))

@section('content')
<section class="card">
  <div class="card-header">
    <h4 class="card-title mb-0">{{ $variant->label() }}</h4>
  </div>
  <div class="card-body">
    <div
      id="export-waiting"
      data-status-url="{{ $result['status_url'] }}"
      data-download-url="{{ $result['download_url'] }}"
      data-ready-text="{{ __('Report ready. Starting download…') }}"
      data-stopped-text="{{ __('Generation stopped unexpectedly. Please retry.') }}"
      data-failed-text="{{ __('Report generation failed. Please retry.') }}"
    >
      <p id="export-status">{{ __('Your report is being prepared. Please wait…') }}</p>
      <div class="progress progress-bar-container">
        <div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div>
      </div>
    </div>
    <p class="mt-1 mb-0">
      <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary btn-sm">
        {{ __('Back') }}
      </a>
    </p>
  </div>
</section>
@endsection

@section('page-script')
  <script src="{{ asset('js/scripts/pages/report-export-waiting.js') }}"></script>
@endsection
