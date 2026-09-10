@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation request') . ' #' . $request->id)

@section('content')
<section>
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between">
      <h4 class="card-title mb-0">{{ __('Valuation request') }} #{{ $request->id }}</h4>
      <span class="badge bg-primary">{{ $request->state }}</span>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4"><strong>{{ __('Number') }}:</strong> {{ $request->number }}</div>
        <div class="col-md-4"><strong>{{ __('Deposit number') }}:</strong> {{ $request->deposit_number }}</div>
        <div class="col-md-4">
          <strong>{{ __('Qima') }}:</strong>
          {{ $request->uploaded_on_qima ? __('Yes') : __('No') }}
        </div>
      </div>
      @if ($request->property)
        <hr>
        <h5>{{ __('Property') }}</h5>
        <p>{{ $request->property->customer_name }} / {{ $request->property->owner_name }}</p>
        <p>{{ $request->property->instrument_no }}</p>
      @endif
    </div>
  </div>

  @can('uploadOfficialReport', $request)
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">{{ __('Official Qima report') }}</h4>
      </div>
      <div class="card-body">
        @if ($request->official_report_path)
          <p class="text-muted">{{ __('Current file') }}: {{ $request->official_report_path }}</p>
        @endif
        @if ($request->isQimaLocked())
          <div class="alert alert-warning mb-0">{{ __('This request is locked after official Qima submission.') }}</div>
        @else
          <form method="post" action="{{ route('dashboard.valuation-requests.official-report', $request) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-1">
              <input type="file" name="official_report" class="form-control" required>
              @error('official_report') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Upload and lock') }}</button>
          </form>
        @endif
      </div>
    </div>
  @endcan
</section>
@endsection
