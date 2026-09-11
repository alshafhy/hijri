@extends('layouts/contentLayoutMaster')

@section('title', __('Create valuation'))

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('Create valuation') }}</h4>
    </div>
    <form method="post" action="{{ route('dashboard.valuation-requests.store') }}">
      @csrf
      <div class="card-body">
        <div class="row g-1">
          <div class="col-md-4">
            <label class="form-label">{{ __('Number') }}</label>
            <input type="text" name="number" class="form-control" value="{{ old('number') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Deposit number') }}</label>
            <input type="text" name="deposit_number" class="form-control" value="{{ old('deposit_number') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Company') }}</label>
            <select name="company_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($companies as $id => $name)
                <option value="{{ $id }}" @selected(old('company_id') == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Evaluator') }}</label>
            <select name="evaluator_user_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($evaluators as $id => $name)
                <option value="{{ $id }}" @selected(old('evaluator_user_id') == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coordinator') }}</label>
            <select name="coordinator_user_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($coordinators as $id => $name)
                <option value="{{ $id }}" @selected(old('coordinator_user_id') == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Customer') }}</label>
            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Owner') }}</label>
            <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Property kind') }}</label>
            <input type="text" name="property_kind" class="form-control" value="{{ old('property_kind') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Property type') }}</label>
            <input type="text" name="property_type" class="form-control" value="{{ old('property_type') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Instrument number') }}</label>
            <input type="text" name="instrument_no" class="form-control" value="{{ old('instrument_no') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('City') }}</label>
            <select name="city_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($cities as $id => $name)
                <option value="{{ $id }}" @selected(old('city_id') == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Street') }}</label>
            <input type="text" name="street" class="form-control" value="{{ old('street') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">X</label>
            <input type="text" name="x_axis" class="form-control" value="{{ old('x_axis') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Y</label>
            <input type="text" name="y_axis" class="form-control" value="{{ old('y_axis') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coordinator share') }}</label>
            <input type="number" name="coordinator_share" class="form-control" value="{{ old('coordinator_share', 0) }}" min="0" max="100">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Evaluator share') }}</label>
            <input type="number" name="evaluator_share" class="form-control" value="{{ old('evaluator_share', 0) }}" min="0" max="100">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Manager share') }}</label>
            <input type="number" name="manager_share" class="form-control" value="{{ old('manager_share', 0) }}" min="0" max="100">
          </div>
        </div>
        @foreach ($errors->all() as $error)
          <div class="text-danger mt-1">{{ $error }}</div>
        @endforeach
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.valuation-requests.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
