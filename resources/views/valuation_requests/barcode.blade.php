@extends('layouts/contentLayoutMaster')

@section('title', __('Barcode') . ' #' . $request->id)

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="card-title mb-0">{{ __('Barcode') }} #{{ $request->id }}</h4>
      <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
    </div>
    <div class="card-body text-center py-3">
      <p class="mb-1"><strong>{{ __('Reference') }}:</strong> {{ $request->reference }}</p>
      <p class="mb-2"><strong>{{ __('Number') }}:</strong> {{ $request->number }}</p>
      <svg id="barcode"></svg>
      <p class="mt-2 text-muted small">{{ $request->reference ?? $request->number ?? $request->id }}</p>
      <button type="button" class="btn btn-primary mt-1" onclick="window.print()">{{ __('Print') }}</button>
    </div>
  </div>
</section>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
  (function () {
    var value = @json((string) ($request->reference ?? $request->number ?? $request->id));
    if (window.JsBarcode && value) {
      JsBarcode('#barcode', value, { format: 'CODE128', displayValue: true, fontSize: 16, height: 80 });
    }
  })();
</script>
@endsection
