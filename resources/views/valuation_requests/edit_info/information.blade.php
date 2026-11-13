@php
  $opt = config('valuation_form');
  $select = config('constants.select');
  $landVal = static fn (string $field) => old("land.$field", $land?->{$field});
  $landList = static fn (string $field) => old("land.$field", $csv($land?->{$field}));
  $flooring = $property->flooring;
@endphp

<h5 class="mb-1">{{ __('Basic data') }}</h5>
<div class="row g-1 mb-2">
  <div class="col-md-3">
    <label class="form-label">{{ __('Customer name') }}</label>
    <input type="text" name="customer_name" class="form-control" maxlength="128" placeholder="{{ __('Enter customer name') }}" value="{{ old('customer_name', $property->customer_name) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Owner name') }}</label>
    <input type="text" name="owner_name" class="form-control" maxlength="128" placeholder="{{ __('Enter owner name') }}" value="{{ old('owner_name', $property->owner_name) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Customer (English)') }}</label>
    <input type="text" name="customer_name_en" class="form-control" dir="ltr" maxlength="250" value="{{ old('customer_name_en', $property->customer_name_en) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Owner (English)') }}</label>
    <input type="text" name="owner_name_en" class="form-control" dir="ltr" maxlength="200" value="{{ old('owner_name_en', $property->owner_name_en) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Instrument number') }}</label>
    <input type="text" name="instrument_no" class="form-control" maxlength="200" value="{{ old('instrument_no', $property->instrument_no) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Property kind') }} / {{ __('Property type') }}</label>
    <input type="text" class="form-control" value="{{ $property->property_kind }} / {{ $property->property_type }}" disabled>
    <small class="text-muted">{{ __('Change it from the request page (Change property type).') }}</small>
  </div>
</div>

<h5 class="mb-1">{{ __('Site and land') }}</h5>
<div class="row g-1 mb-1">
  <div class="col-md-4">
    <label class="form-label">{{ __('Site') }}</label>
    <select name="land[site]" class="form-select">
      <option value="">{{ __('Select') }}</option>
      @foreach ($opt['site'] as $value)
        <option value="{{ $value }}" @selected($landVal('site') === $value)>{{ $value }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Land nature') }}</label>
    <select name="land[land_nature]" class="form-select">
      <option value="">{{ __('Select') }}</option>
      @foreach ($opt['land_nature'] as $value)
        <option value="{{ $value }}" @selected($landVal('land_nature') === $value)>{{ $value }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Street width') }}</label>
    <input type="number" step="any" name="land[street_width]" class="form-control" value="{{ $landVal('street_width') }}">
  </div>
</div>
<div class="row g-1 mb-2">
  <div class="col-md-6">
    <label class="form-label d-block">{{ __('Facades') }}</label>
    @include('valuation_requests.partials.check-group', ['name' => 'land[facade]', 'options' => $opt['facade'], 'selected' => $landList('facade')])
  </div>
  <div class="col-md-6">
    <label class="form-label d-block">{{ __('Streets') }}</label>
    @include('valuation_requests.partials.check-group', ['name' => 'land[street]', 'options' => $opt['street'], 'selected' => $landList('street')])
  </div>
  <div class="col-md-6">
    <label class="form-label d-block">{{ __('Neighbors') }}</label>
    @include('valuation_requests.partials.check-group', ['name' => 'land[neighbor]', 'options' => $opt['neighbor'], 'selected' => $landList('neighbor')])
  </div>
  <div class="col-md-6">
    <label class="form-label d-block">{{ __('Services') }}</label>
    @php
      $checkedServices = old('services', collect(array_keys($opt['services']))->filter(fn ($k) => $service?->{$k} === 'نعم')->values()->all());
    @endphp
    @include('valuation_requests.partials.check-group', ['name' => 'services', 'options' => $opt['services'], 'selected' => $checkedServices])
  </div>
  <div class="col-md-12">
    <label class="form-label" for="surrounding-facilities">{{ __('Surrounding facilities') }}</label>
    <select name="land[surrounding_facilities][]" id="surrounding-facilities" class="select2 form-select" multiple>
      @foreach ($select['surrounding_facilities']['options'] as $value => $label)
        <option value="{{ $value }}" @selected(in_array($value, $landList('surrounding_facilities'), true))>{{ $label }}</option>
      @endforeach
    </select>
  </div>
</div>

<h5 class="mb-1">{{ __('Ownership and use') }}</h5>
<div class="row g-1 mb-2">
  @foreach ([
    'ownership_type' => [__('Ownership type'), $select['ownership_type']],
    'furnishing_status' => [__('Furnishing status'), $select['furnishing_status']],
    'building_type' => [__('Building type'), $select['building_type']],
    'finishing_status' => [__('Finishing status'), $select['finishing_status']],
  ] as $field => [$label, $options])
    <div class="col-md-3">
      <label class="form-label">{{ $label }}</label>
      <select name="land[{{ $field }}]" class="form-select">
        <option value="">{{ __('Select') }}</option>
        @foreach ($options as $value => $text)
          <option value="{{ $value }}" @selected((string) $landVal($field) === (string) $value)>{{ $text }}</option>
        @endforeach
      </select>
    </div>
  @endforeach
  <div class="col-md-3">
    <label class="form-label">{{ __('Property use') }}</label>
    <select name="land[property_use]" class="form-select">
      <option value="">{{ __('Select') }}</option>
      @foreach ($select['origin_sector'] as $value => $text)
        <option value="{{ $value }}" @selected((string) $landVal('property_use') === (string) $value)>{{ $text }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Sector description') }}</label>
    <input type="text" name="land[sector_description]" class="form-control" maxlength="100" value="{{ $landVal('sector_description') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Ownership description') }}</label>
    <input type="text" name="land[ownership_description]" class="form-control" maxlength="100" value="{{ $landVal('ownership_description') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Total building area') }}</label>
    <input type="number" step="any" name="land[total_building_area]" class="form-control" value="{{ $landVal('total_building_area') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Approved floors') }}</label>
    <input type="number" step="any" name="land[approved_floors]" class="form-control" value="{{ $landVal('approved_floors') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Approved building ratio') }}</label>
    <input type="number" step="any" name="land[approved_building]" class="form-control" value="{{ $landVal('approved_building') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('Additional features') }}</label>
    <input type="text" name="land[additional_features]" class="form-control" maxlength="100" value="{{ $landVal('additional_features') }}">
  </div>
</div>

<h5 class="mb-1">{{ __('Meters') }}</h5>
<div class="row g-1 mb-2">
  <div class="col-md-3">
    <label class="form-label">{{ __('Electricity meters count') }}</label>
    <input type="text" name="land[electricity_no]" class="form-control" maxlength="45" value="{{ $landVal('electricity_no') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Electricity meter number') }}</label>
    <input type="text" name="land[electricity_num]" class="form-control" maxlength="45" value="{{ $landVal('electricity_num') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Water meters count') }}</label>
    <input type="text" name="land[water_no]" class="form-control" maxlength="45" value="{{ $landVal('water_no') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Water meter number') }}</label>
    <input type="text" name="land[water_num]" class="form-control" maxlength="45" value="{{ $landVal('water_num') }}">
  </div>
</div>

@if ($isBuilding)
  <h5 class="mb-1">{{ __('Building details') }}</h5>
  <div class="row g-1 mb-1">
    <div class="col-md-6">
      <label class="form-label d-block">{{ __('External doors') }}</label>
      @include('valuation_requests.partials.check-group', ['name' => 'land[door_ex]', 'options' => $select['door_ex']['options'], 'selected' => $landList('door_ex')])
    </div>
    <div class="col-md-6">
      <label class="form-label d-block">{{ __('Internal doors') }}</label>
      @include('valuation_requests.partials.check-group', ['name' => 'land[door_in]', 'options' => $select['door_in']['options'], 'selected' => $landList('door_in')])
    </div>
    <div class="col-md-12">
      <label class="form-label d-block">{{ __('Includes') }}</label>
      @include('valuation_requests.partials.check-group', ['name' => 'land[include]', 'options' => $opt['include'], 'selected' => $landList('include')])
    </div>
    @foreach (['heater_no' => __('Heaters'), 'lift_no' => __('Lifts'), 'bathroom_a_no' => __('Arabic bathrooms'), 'bathroom_e_no' => __('Western bathrooms')] as $field => $label)
      <div class="col-md-3">
        <label class="form-label">{{ $label }}</label>
        <input type="number" min="0" name="land[{{ $field }}]" class="form-control" value="{{ $landVal($field) }}">
      </div>
    @endforeach
  </div>

  <h6 class="mt-1">{{ __('Facade materials') }}</h6>
  <div class="row g-1 mb-1">
    @foreach ($opt['facade_sides'] as $side => $label)
      <div class="col-md-6">
        <label class="form-label d-block">{{ $label }}</label>
        @include('valuation_requests.partials.check-group', [
          'name' => "facades[$side]",
          'options' => $select['facade_typen']['options'],
          'selected' => old("facades.$side", $csv($facade?->{'facade_type_'.$side})),
        ])
      </div>
    @endforeach
  </div>

  <h6 class="mt-1">{{ __('Flooring') }}</h6>
  <div class="row g-1 mb-1">
    @foreach ($opt['flooring_rooms'] as $room => $label)
      <div class="col-md-6">
        <label class="form-label d-block">{{ $label }}</label>
        @include('valuation_requests.partials.check-group', [
          'name' => "flooring[$room]",
          'options' => $select['flooring_type']['options'],
          'selected' => old("flooring.$room", $csv($flooring?->{'flooring_'.$room})),
        ])
        <input type="text" name="flooring[{{ $room }}_description]" class="form-control form-control-sm mt-50" maxlength="100"
               placeholder="{{ __('Other / description') }}" value="{{ old("flooring.{$room}_description", $flooring?->{$room.'_description'}) }}">
      </div>
    @endforeach
  </div>
@endif
