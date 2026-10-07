@php $loc = $property->location; @endphp
<div class="row g-1 mb-1">
  <div class="col-md-4">
    <label class="form-label" for="location-city">{{ __('City') }}</label>
    <select name="location[city_id]" id="location-city" class="form-select" data-neighborhood-target="#location-neighborhood">
      <option value="">{{ __('Select') }}</option>
      @foreach ($cities as $id => $name)
        <option value="{{ $id }}" @selected(old('location.city_id', $loc?->city_id) == $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="location-neighborhood">{{ __('Neighborhood') }}</label>
    <select name="location[neighborhood_id]" id="location-neighborhood" class="form-select">
      <option value="">{{ __('Select') }}</option>
      @foreach ($neighborhoods as $id => $name)
        <option value="{{ $id }}" @selected(old('location.neighborhood_id', $loc?->neighborhood_id) == $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Street') }}</label>
    <input type="text" name="location[street]" class="form-control" maxlength="45" value="{{ old('location.street', $loc?->street) }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Plan number') }}</label>
    <input type="text" name="location[sketch_no]" class="form-control" maxlength="45" value="{{ old('location.sketch_no', $loc?->sketch_no) }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Block number') }}</label>
    <input type="text" name="location[part_no]" class="form-control" maxlength="45" value="{{ old('location.part_no', $loc?->part_no) }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Plot number') }}</label>
    <input type="text" name="location[piece_number]" class="form-control" maxlength="100" value="{{ old('location.piece_number', $loc?->piece_number) }}">
  </div>
</div>

@include('valuation_requests.partials.map-picker', [
  'mapId' => 'evaluator-map',
  'latName' => 'location[x_axis]',
  'lngName' => 'location[y_axis]',
  'lat' => old('location.x_axis', $loc?->x_axis),
  'lng' => old('location.y_axis', $loc?->y_axis),
])

@include('valuation_requests.partials.neighborhood-script')
