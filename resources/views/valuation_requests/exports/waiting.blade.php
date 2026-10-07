@extends('layouts/contentLayoutMaster')

@section('title', __('Preparing report'))

@section('content')
<section class="card">
  <div class="card-header">
    <h4 class="card-title mb-0">{{ $variant->label() }}</h4>
  </div>
  <div class="card-body">
    <p id="export-status">{{ __('Your report is being prepared. Please wait…') }}</p>
    <div class="progress" style="height: 8px;">
      <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
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
<script>
(function () {
  const statusUrl = @json($result['status_url']);
  const downloadUrl = @json($result['download_url']);
  const statusEl = document.getElementById('export-status');

  function poll() {
    fetch(statusUrl, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.ready) {
          statusEl.textContent = @json(__('Report ready. Starting download…'));
          window.location.href = downloadUrl;
          return;
        }
        if (!data.generating) {
          statusEl.textContent = @json(__('Generation stopped unexpectedly. Please retry.'));
          return;
        }
        setTimeout(poll, 2000);
      })
      .catch(function () {
        setTimeout(poll, 3000);
      });
  }

  poll();
})();
</script>
@endsection
