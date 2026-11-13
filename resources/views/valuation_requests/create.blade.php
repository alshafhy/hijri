@extends('layouts/contentLayoutMaster')

@section('title', __('Create valuation'))

@section('vendor-style')
  @include('valuation_requests.partials.hijri-picker-style')
@endsection

@section('vendor-script')
  @include('valuation_requests.partials.hijri-picker-script')
@endsection

@section('content')
@php
  $clientType = (int) old('client_type', \App\Actions\Valuation\CreateValuationRequestAction::CLIENT_CONTRACTOR);
  $tabs = ['initial' => __('Initial information'), 'detailed' => __('Detailed information'), 'map' => __('Location')];
@endphp
<section>
  @include('valuation_requests.partials.flash')

  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('Create valuation') }}</h4>
    </div>
    <form method="post" action="{{ route('dashboard.valuation-requests.store') }}" enctype="multipart/form-data" data-wizard
          data-offers-url="{{ route('dashboard.valuation-lookups.partner-offers', ['partner' => '__ID__']) }}"
          data-estates-url="{{ route('dashboard.valuation-lookups.offer-estates', ['offer' => '__ID__']) }}"
          data-offer-placeholder="{{ __('Price offer') }}"
          data-estate-placeholder="{{ __('Estate') }}">
      @csrf
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
            <h6>{{ __('Client') }}</h6>
            <div class="row g-1 mb-2">
              <div class="col-md-4">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="client_type" id="client-contractor" value="1" @checked($clientType === 1)>
                  <label class="form-check-label" for="client-contractor">{{ __('Contractor') }}</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="client_type" id="client-partner" value="2" @checked($clientType === 2)>
                  <label class="form-check-label" for="client-partner">{{ __('Partner') }}</label>
                </div>
              </div>
              <div class="col-md-8" data-client="1">
                <select name="contractor_id" id="contractor_id" class="form-select" @if ($clientType === 1) required @endif>
                  <option value="">{{ __('Select contractor') }}</option>
                  @foreach ($contractors as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('contractor_id') === (string) $id)>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-8" data-client="2">
                <div class="row g-1">
                  <div class="col-md-4">
                    <select name="partner_id" id="partner_id" class="form-select" @if ($clientType === 2) required @endif>
                      <option value="">{{ __('Select partner') }}</option>
                      @foreach ($partners as $id => $name)
                        <option value="{{ $id }}" @selected((string) old('partner_id') === (string) $id)>{{ $name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <select id="offer_id" class="form-select"><option value="">{{ __('Price offer') }}</option></select>
                  </div>
                  <div class="col-md-4">
                    <select name="offer_estate_id" id="offer_estate_id" class="form-select"><option value="">{{ __('Estate') }}</option></select>
                  </div>
                </div>
              </div>
            </div>
            @include('valuation_requests.form.initial', ['p' => null, 'request' => null])
          </div>

          <div class="tab-pane fade" id="tab-detailed" role="tabpanel">
            @include('valuation_requests.form.detailed', ['p' => null, 'request' => null])
            <h6 class="mt-2">{{ __('Fee shares') }}</h6>
            <div class="row g-1">
              @foreach (['coordinator' => __('Coordinator share'), 'evaluator' => __('Evaluator share'), 'manager' => __('Manager share')] as $role => $label)
                <div class="col-md-4">
                  <label class="form-label">{{ $label }} %</label>
                  <input type="number" name="{{ $role }}_share" class="form-control" min="0" max="100" value="{{ old("{$role}_share", $defaultShares[$role]) }}">
                </div>
              @endforeach
            </div>
          </div>

          <div class="tab-pane fade" id="tab-map" role="tabpanel">
            @include('valuation_requests.partials.map-picker', ['mapId' => 'create-map', 'latName' => 'x_axis', 'lngName' => 'y_axis', 'lat' => old('x_axis'), 'lng' => old('y_axis')])
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.valuation-requests.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        <div class="d-flex gap-50">
          <button type="button" class="btn btn-outline-primary" data-wizard-prev>{{ __('Previous') }}</button>
          <button type="button" class="btn btn-primary" data-wizard-next>{{ __('Next') }}</button>
          <button type="submit" class="btn btn-success d-none" data-wizard-submit>{{ __('Save') }}</button>
        </div>
      </div>
    </form>
  </div>
</section>
@include('valuation_requests.partials.wizard-script')
@endsection

@push('page-script')
  <script src="{{ asset('js/scripts/pages/valuation-create-lookups.js') }}"></script>
@endpush
