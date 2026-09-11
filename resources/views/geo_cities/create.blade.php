@extends('layouts/contentLayoutMaster')

@section('title', __('Add city'))

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.geo-cities.store') }}">
      @csrf
      <div class="card-header"><h4 class="card-title">{{ __('Add city') }}</h4></div>
      <div class="card-body row g-2">
        <div class="col-md-6">
          <label class="form-label">{{ __('Arabic name') }}</label>
          <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="form-control @error('name_ar') is-invalid @enderror" required>
          @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">{{ __('English name') }}</label>
          <input type="text" name="name_en" value="{{ old('name_en') }}" class="form-control @error('name_en') is-invalid @enderror">
          @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.geo-cities.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
