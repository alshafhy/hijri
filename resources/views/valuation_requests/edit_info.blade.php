@extends('layouts/contentLayoutMaster')

@section('title', __('Evaluator edit') . ' #' . $request->id)

@section('content')
@php
  $loc = $request->property?->location;
  $componentKeys = [
    'land_area', 'share_land', 'flat_area', 'basement', 'floor_g', 'floor_a', 'floor_u',
    'annexe', 'fence', 'parking', 'land_garden', 'pool', 'ground', 'upper', 'shop',
    'apartment', 'office', 'exhibition',
  ];
@endphp
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('Evaluator edit') }} #{{ $request->id }}</h4>
    </div>
    <form method="post" action="{{ route('dashboard.valuation-requests.update-info', $request) }}">
      @csrf
      @method('PUT')
      <div class="card-body">
        <div class="row g-1">
          <div class="col-md-4">
            <label class="form-label">{{ __('Customer') }}</label>
            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $request->property?->customer_name) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Owner') }}</label>
            <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $request->property?->owner_name) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Instrument number') }}</label>
            <input type="text" name="instrument_no" class="form-control" value="{{ old('instrument_no', $request->property?->instrument_no) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Property kind') }}</label>
            <input type="text" name="property_kind" class="form-control" value="{{ old('property_kind', $request->property?->property_kind) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Property type') }}</label>
            <input type="text" name="property_type" class="form-control" value="{{ old('property_type', $request->property?->property_type) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Evaluation date') }}</label>
            <input type="date" name="evaluation_date" class="form-control" value="{{ old('evaluation_date', optional($request->property?->evaluation_date)->format('Y-m-d')) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Forced sale %') }}</label>
            <input type="number" name="forced_sale_percentage" class="form-control" min="0" max="100"
                   value="{{ old('forced_sale_percentage', $request->property?->forced_sale_percentage) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('City') }}</label>
            <select name="location[city_id]" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($cities as $id => $name)
                <option value="{{ $id }}" @selected(old('location.city_id', $loc?->city_id) == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Street') }}</label>
            <input type="text" name="location[street]" class="form-control" value="{{ old('location.street', $loc?->street) }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">X</label>
            <input type="text" name="location[x_axis]" class="form-control" value="{{ old('location.x_axis', $loc?->x_axis) }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Y</label>
            <input type="text" name="location[y_axis]" class="form-control" value="{{ old('location.y_axis', $loc?->y_axis) }}">
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('Notes') }}</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $request->property?->notes) }}</textarea>
          </div>
        </div>

        <h5 class="mt-2">{{ __('Components') }}</h5>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>{{ __('Component') }}</th>
                <th>{{ __('Area') }}</th>
                <th>{{ __('Price') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($componentKeys as $key)
                @php $comp = $components->get($key); @endphp
                <tr>
                  <td>{{ __($key) }}</td>
                  <td>
                    <input type="number" step="any" class="form-control form-control-sm"
                           name="components[{{ $key }}][area_value]"
                           value="{{ old("components.$key.area_value", $comp?->area_value) }}">
                  </td>
                  <td>
                    <input type="number" step="any" class="form-control form-control-sm"
                           name="components[{{ $key }}][price_value]"
                           value="{{ old("components.$key.price_value", $comp?->price_value) }}">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @foreach ($errors->all() as $error)
          <div class="text-danger">{{ $error }}</div>
        @endforeach
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
