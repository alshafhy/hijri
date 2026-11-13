@php
  $opt = config('valuation_form');
  $building = $property->building;
  $bVal = static fn (string $field) => old("building.$field", $building?->{$field});
@endphp
<div class="row g-1 mb-2">
  @foreach ([
    'architectural' => __('Architectural style'),
    'quality_insulation' => __('Insulation quality'),
    'structure_construction' => __('Structure'),
    'bishop_type' => __('Roof type'),
    'build_state' => __('Building condition'),
  ] as $field => $label)
    <div class="col-md-4">
      <label class="form-label">{{ $label }}</label>
      <select name="building[{{ $field }}]" class="form-select">
        <option value="">{{ __('Select') }}</option>
        @foreach ($opt[$field] as $value)
          <option value="{{ $value }}" @selected($bVal($field) === $value)>{{ $value }}</option>
        @endforeach
      </select>
    </div>
  @endforeach
  <div class="col-md-4">
    <label class="form-label" for="build-age">{{ __('Building age') }}</label>
    <select name="building[build_age]" id="build-age" class="form-select" data-calc>
      <option value="">{{ __('Select') }}</option>
      <option value="جديد" @selected($bVal('build_age') === 'جديد')>{{ __('New') }}</option>
      @for ($year = 1; $year <= $opt['max_build_age']; $year++)
        <option value="{{ $year }}" @selected((string) $bVal('build_age') === (string) $year)>{{ $year }}</option>
      @endfor
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="default-build-age">{{ __('Default building life') }}</label>
    <input type="number" min="0" name="building[default_build_age]" id="default-build-age" class="form-control" data-calc value="{{ $bVal('default_build_age') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="remaining-build-age">{{ __('Remaining building life') }}</label>
    <input type="number" min="0" name="building[remaining_build_age]" id="remaining-build-age" class="form-control" data-calc value="{{ $bVal('remaining_build_age') }}">
  </div>
</div>

<h6>{{ __('Air conditioning') }}</h6>
<div class="row g-1">
  <div class="col-md-9">
    @include('valuation_requests.partials.check-group', [
      'name' => 'ac[types]',
      'options' => $opt['ac_types'],
      'selected' => old('ac.types', $csv($property->ac?->types)),
    ])
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('AC units count') }}</label>
    <input type="number" min="0" name="ac[ac_count]" class="form-control" value="{{ old('ac.ac_count', $property->ac?->ac_count) }}">
  </div>
</div>
