@extends('layouts/contentLayoutMaster')

@section('title', __('Edit valuation') . ' #' . $request->id)

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('Coordinator edit') }} #{{ $request->id }}</h4>
    </div>
    <form method="post" action="{{ route('dashboard.valuation-requests.update', $request) }}">
      @csrf
      @method('PUT')
      <div class="card-body">
        <div class="row g-1">
          <div class="col-md-4">
            <label class="form-label">{{ __('Number') }}</label>
            <input type="text" name="number" class="form-control" value="{{ old('number', $request->number) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Deposit number') }}</label>
            <input type="text" name="deposit_number" class="form-control" value="{{ old('deposit_number', $request->deposit_number) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Company') }}</label>
            <select name="company_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($companies as $id => $name)
                <option value="{{ $id }}" @selected(old('company_id', $request->company_id) == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Evaluator') }}</label>
            <select name="evaluator_user_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($evaluators as $id => $name)
                <option value="{{ $id }}" @selected(old('evaluator_user_id', $request->evaluator_user_id) == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
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
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.valuation-requests.show', $request) }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
