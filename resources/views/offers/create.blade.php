@extends('layouts/contentLayoutMaster')

@section('title', __('Add offer'))

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.offers.store') }}">
      @csrf
      <div class="card-header"><h4 class="card-title">{{ __('Add offer') }}</h4></div>
      <div class="card-body">@include('offers._form')</div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.offers.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
