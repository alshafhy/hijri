@extends('layouts/contentLayoutMaster')

@section('title', __('Edit initial data') . ' #' . $request->reference)

@section('vendor-style')
  @include('valuation_requests.partials.hijri-picker-style')
@endsection

@section('vendor-script')
  @include('valuation_requests.partials.hijri-picker-script')
@endsection

@section('content')
@php
  $p = $request->property;
  $tabs = ['initial' => __('Initial information'), 'detailed' => __('Detailed information'), 'map' => __('Location')];
@endphp
<section>
  @include('valuation_requests.partials.flash')

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="card-title">{{ __('Edit initial data') }} — {{ __('Reference') }} {{ $request->reference }}</h4>
      <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
    </div>
    <form method="post" action="{{ route('dashboard.valuation-requests.update', $request) }}" enctype="multipart/form-data" data-wizard>
      @csrf
      @method('PUT')
      <div class="card-body">
        <div class="progress mb-1" style="height: 6px;"><div class="progress-bar" data-wizard-progress style="width: 33%"></div></div>
        <ul class="nav nav-tabs" role="tablist">
          @foreach ($tabs as $key => $label)
            <li class="nav-item">
              <button type="button" class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#tab-{{ $key }}" role="tab">{{ $loop->iteration }}. {{ $label }}</button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content pt-1">
          <div class="tab-pane fade show active" id="tab-initial" role="tabpanel">
            @include('valuation_requests.form.initial', ['p' => $p])
            <div class="row g-1 mt-50">
              <div class="col-md-4">
                <label class="form-label" for="deposit_number">{{ __('Deposit number') }}</label>
                <input type="text" name="deposit_number" id="deposit_number" class="form-control" value="{{ old('deposit_number', $request->deposit_number) }}">
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-detailed" role="tabpanel">
            @include('valuation_requests.form.detailed', ['p' => $p])

            <h6 class="mt-2">{{ __('Attachments') }}</h6>
            @if ($request->attachments->isEmpty())
              <p class="text-muted">{{ __('No attachments uploaded yet.') }}</p>
            @else
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead class="table-light"><tr><th>#</th><th>{{ __('File') }}</th><th>{{ __('Type') }}</th><th></th></tr></thead>
                  <tbody>
                    @foreach ($request->attachments as $attachment)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('dashboard.valuation-requests.attachments.download', [$request, $attachment]) }}">{{ $attachment->displayName() }}</a></td>
                        <td>{{ $attachment->type ?: '—' }}</td>
                        <td class="text-end">
                          <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-submit="delete-attachment-{{ $attachment->id }}">{{ __('Delete') }}</button>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>

          <div class="tab-pane fade" id="tab-map" role="tabpanel">
            @include('valuation_requests.partials.map-picker', [
                'mapId' => 'edit-map', 'latName' => 'x_axis', 'lngName' => 'y_axis',
                'lat' => old('x_axis', $p?->location?->x_axis), 'lng' => old('y_axis', $p?->location?->y_axis),
            ])
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        <div class="d-flex gap-50">
          <button type="button" class="btn btn-outline-primary" data-wizard-prev>{{ __('Previous') }}</button>
          <button type="button" class="btn btn-primary" data-wizard-next>{{ __('Next') }}</button>
          <button type="submit" class="btn btn-success d-none" data-wizard-submit>{{ __('Save') }}</button>
        </div>
      </div>
    </form>
  </div>

  @foreach ($request->attachments as $attachment)
    <form method="post" id="delete-attachment-{{ $attachment->id }}" action="{{ route('dashboard.valuation-requests.attachments.destroy', [$request, $attachment]) }}" class="d-none">
      @csrf @method('DELETE')
    </form>
  @endforeach
</section>
@include('valuation_requests.partials.wizard-script')
@endsection

@push('page-script')
  <script>
    document.querySelectorAll('[data-confirm-submit]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (confirm(@json(__('Are you sure?')))) {
          document.getElementById(btn.dataset.confirmSubmit).submit();
        }
      });
    });
  </script>
@endpush
