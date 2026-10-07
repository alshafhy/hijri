@extends('layouts/contentLayoutMaster')

@section('title', __('Evaluator edit') . ' #' . $request->reference)

@section('content')
@php
  $csv = static fn ($value): array => array_values(array_filter(array_map('trim', explode(',', (string) $value)), static fn ($v) => $v !== ''));
  $land = $property->lands->first();
  $facade = $property->facades->first();
  $service = $property->services->first();
  $tabs = [
      'location' => __('Location'),
      'information' => __('Property information'),
      'components' => __('Property components'),
      'specs' => __('Property specifications'),
      'photos' => __('Photos'),
      'valuation' => __('Valuation'),
      'comparables' => __('Comparables'),
      'standard' => __('Other information'),
      'attachments' => __('Attachments'),
  ];
  if (! $isBuilding) {
      unset($tabs['specs']);
  }
  $sections = ['land', 'facades', 'services', 'question', 'components', 'totals', 'comparables', 'custom_table'];
  if ($isBuilding) {
      array_push($sections, 'flooring', 'building', 'ac');
  }
@endphp
<section>
  @include('valuation_requests.partials.flash')

  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-1">
      <div>
        <h4 class="card-title mb-25">{{ __('Evaluator edit') }} — {{ __('Reference') }} {{ $request->reference }}</h4>
        <small class="text-muted">{{ $property->property_kind }} / {{ $property->property_type }} — {{ $property->customer_name }}</small>
      </div>
      <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back') }}</a>
    </div>

    <form method="post" action="{{ route('dashboard.valuation-requests.update-info', $request) }}" enctype="multipart/form-data" id="evaluator-form">
      @csrf
      @method('PUT')
      @foreach ($sections as $section)
        <input type="hidden" name="sections[]" value="{{ $section }}">
      @endforeach

      <div class="card-body">
        <ul class="nav nav-tabs flex-wrap" role="tablist">
          @foreach ($tabs as $key => $label)
            <li class="nav-item">
              <button type="button" class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#tab-{{ $key }}" role="tab">
                {{ $loop->iteration }}. {{ $label }}
              </button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content pt-1">
          <div class="tab-pane fade show active" id="tab-location" role="tabpanel">
            @include('valuation_requests.edit_info.location')
          </div>
          <div class="tab-pane fade" id="tab-information" role="tabpanel">
            @include('valuation_requests.edit_info.information')
          </div>
          <div class="tab-pane fade" id="tab-components" role="tabpanel">
            <label class="form-label" for="notes">{{ __('Property components description') }}</label>
            <textarea name="notes" id="notes" class="form-control" rows="8">{{ old('notes', $property->notes) }}</textarea>
          </div>
          @if ($isBuilding)
            <div class="tab-pane fade" id="tab-specs" role="tabpanel">
              @include('valuation_requests.edit_info.specs')
            </div>
          @endif
          <div class="tab-pane fade" id="tab-photos" role="tabpanel">
            @include('valuation_requests.edit_info.photos')
          </div>
          <div class="tab-pane fade" id="tab-valuation" role="tabpanel">
            @include('valuation_requests.edit_info.valuation')
          </div>
          <div class="tab-pane fade" id="tab-comparables" role="tabpanel">
            @include('valuation_requests.edit_info.comparables')
          </div>
          <div class="tab-pane fade" id="tab-standard" role="tabpanel">
            @include('valuation_requests.edit_info.standard')
          </div>
          <div class="tab-pane fade" id="tab-attachments" role="tabpanel">
            @include('valuation_requests.edit_info.attachments')
          </div>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger mt-1 p-1">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
      <div class="card-footer d-flex gap-1">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
      </div>
    </form>
  </div>

  {{-- Delete forms live outside the main form (HTML forbids nesting). --}}
  @foreach ($property->pictures as $picture)
    <form method="post" id="delete-picture-{{ $picture->id }}" action="{{ route('dashboard.valuation-requests.pictures.destroy', [$request, $picture]) }}" class="d-none">
      @csrf @method('DELETE')
    </form>
  @endforeach
  @foreach ($request->attachments as $attachment)
    <form method="post" id="delete-attachment-{{ $attachment->id }}" action="{{ route('dashboard.valuation-requests.attachments.destroy', [$request, $attachment]) }}" class="d-none">
      @csrf @method('DELETE')
    </form>
  @endforeach
</section>
@endsection

@push('page-script')
  <script>
    (function () {
      // Keep the active tab after a validation redirect.
      var form = document.getElementById('evaluator-form');
      var firstError = form.querySelector('.is-invalid');
      if (firstError) {
        var pane = firstError.closest('.tab-pane');
        if (pane) { bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#' + pane.id + '"]')).show(); }
      }
      document.querySelectorAll('[data-confirm-submit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (confirm(@json(__('Are you sure?')))) {
            document.getElementById(btn.dataset.confirmSubmit).submit();
          }
        });
      });
    })();
  </script>
@endpush
