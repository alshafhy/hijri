@extends('layouts/contentLayoutMaster')

@section('title', __('New valuation service contract'))

@section('content')
@php
  $value = fn (string $name, $default = null) => old($name, $default);
@endphp
<section id="service-contract-form">
  <form method="POST" action="{{ route('dashboard.service-contracts.store') }}" class="card">
    @csrf
    <div class="card-header d-flex justify-content-between">
      <h4 class="card-title mb-0">{{ __('New valuation service contract') }}</h4>
      <a href="{{ route('dashboard.service-contracts.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
    </div>
    <div class="card-body">
      <h6 class="text-primary">{{ __('Client (second party)') }}</h6>
      <div class="row g-1 mb-2">
        @foreach ([
            'client_name' => [__('Client name'), 'text', true],
            'client_id_number' => [__('ID / commercial register number'), 'text', true],
            'client_phone' => [__('Phone'), 'text', true],
            'client_email' => [__('Email'), 'email', false],
            'client_address' => [__('Address'), 'text', false],
            'purpose' => [__('Valuation purpose'), 'text', true],
        ] as $name => [$label, $type, $required])
          <div class="col-md-4">
            <label class="form-label" for="{{ $name }}">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value($name) }}" class="form-control @error($name) is-invalid @enderror" @required($required)>
            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        @endforeach
      </div>

      <h6 class="text-primary">{{ __('Property') }}</h6>
      <div class="row g-1 mb-2">
        <div class="col-md-3">
          <label class="form-label" for="city_id">{{ __('City') }} <span class="text-danger">*</span></label>
          <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" required data-neighborhood-target="#neighborhood_id">
            <option value="">{{ __('Select') }}</option>
            @foreach ($cities as $id => $name)
              <option value="{{ $id }}" @selected((string) $value('city_id') === (string) $id)>{{ $name }}</option>
            @endforeach
          </select>
          @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="neighborhood_id">{{ __('Neighborhood') }} <span class="text-danger">*</span></label>
          <select name="neighborhood_id" id="neighborhood_id" class="form-select @error('neighborhood_id') is-invalid @enderror" required data-selected="{{ $value('neighborhood_id') }}">
            <option value="">{{ __('Select') }}</option>
          </select>
          @error('neighborhood_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="property_kind">{{ __('Property kind') }} <span class="text-danger">*</span></label>
          <select name="property_kind" id="property_kind" class="form-select @error('property_kind') is-invalid @enderror" required>
            <option value="">{{ __('Select') }}</option>
            @foreach (array_keys($kinds) as $kind)
              <option value="{{ $kind }}" @selected($value('property_kind') === $kind)>{{ $kind }}</option>
            @endforeach
          </select>
          @error('property_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="property_type">{{ __('Property type') }} <span class="text-danger">*</span></label>
          <select name="property_type" id="property_type" class="form-select @error('property_type') is-invalid @enderror" required data-current="{{ $value('property_type') }}"></select>
          @error('property_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="instrument_no">{{ __('Instrument number') }} <span class="text-danger">*</span></label>
          <input type="text" name="instrument_no" id="instrument_no" value="{{ $value('instrument_no') }}" class="form-control @error('instrument_no') is-invalid @enderror" required>
          @error('instrument_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="instrument_date">{{ __('Instrument date') }}</label>
          <input type="date" name="instrument_date" id="instrument_date" value="{{ $value('instrument_date') }}" class="form-control @error('instrument_date') is-invalid @enderror">
          @error('instrument_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <h6 class="text-primary">{{ __('Contract') }}</h6>
      <div class="row g-1">
        <div class="col-md-4">
          <label class="form-label" for="contract_date">{{ __('Contract date') }} <span class="text-danger">*</span></label>
          <input type="date" name="contract_date" id="contract_date" value="{{ $value('contract_date', now()->toDateString()) }}" class="form-control @error('contract_date') is-invalid @enderror" required>
          @error('contract_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label" for="contract_value">{{ __('Contract value (SAR)') }} <span class="text-danger">*</span></label>
          <input type="number" step="0.01" min="1" name="contract_value" id="contract_value" value="{{ $value('contract_value') }}" class="form-control @error('contract_value') is-invalid @enderror" required>
          @error('contract_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label" for="representative_name">{{ __('First party representative') }} <span class="text-danger">*</span></label>
          <input type="text" name="representative_name" id="representative_name" value="{{ $value('representative_name', $representative) }}" class="form-control @error('representative_name') is-invalid @enderror" required>
          @error('representative_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">{{ __('Create contract') }}</button>
    </div>
  </form>
</section>
@endsection

@include('valuation_requests.partials.neighborhood-script')

@push('page-script')
  <script>
    (function () {
      var kinds = @json($kinds);
      var kind = document.getElementById('property_kind');
      var type = document.getElementById('property_type');
      function fill() {
        var current = type.dataset.current;
        type.innerHTML = '<option value="">' + @json(__('Select')) + '</option>';
        (kinds[kind.value] || []).forEach(function (name) {
          var opt = document.createElement('option');
          opt.value = name; opt.textContent = name;
          if (name === current) { opt.selected = true; }
          type.appendChild(opt);
        });
      }
      kind.addEventListener('change', function () { type.dataset.current = ''; fill(); });
      fill();
    })();
  </script>
@endpush
