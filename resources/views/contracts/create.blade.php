@extends('layouts/contentLayoutMaster')

@section('title', __('Add contract'))

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.contracts.store') }}">
      @csrf
      <div class="card-header"><h4 class="card-title mb-0">{{ __('Add contract') }}</h4></div>
      <div class="card-body">
        <div class="row g-1">
          <div class="col-md-6">
            <label class="form-label">{{ __('Contractor') }}</label>
            <select name="contractor_id" class="form-select @error('contractor_id') is-invalid @enderror" required>
              <option value="">{{ __('Select') }}</option>
              @foreach ($contractors as $id => $name)
                <option value="{{ $id }}" @selected((string) old('contractor_id') === (string) $id)>{{ $name }}</option>
              @endforeach
            </select>
            @error('contractor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Valuation request') }}</label>
            <select name="valuation_request_id" class="form-select @error('valuation_request_id') is-invalid @enderror" required>
              <option value="">{{ __('Select') }}</option>
              @foreach ($valuationRequests as $id => $label)
                <option value="{{ $id }}" @selected((string) old('valuation_request_id') === (string) $id)>{{ $label }}</option>
              @endforeach
            </select>
            @error('valuation_request_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('State') }}</label>
            <select name="state" class="form-select">
              <option value="{{ App\Models\Contract::STATE_UNPAID }}" @selected((string) old('state', '0') === '0')>{{ __('Unpaid') }}</option>
              <option value="{{ App\Models\Contract::STATE_PAID }}" @selected((string) old('state') === '1')>{{ __('Paid') }}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.contracts.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
