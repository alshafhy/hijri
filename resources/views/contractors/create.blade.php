@extends('layouts/contentLayoutMaster')

@section('title', __('Add contractor'))

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.contractors.store') }}">
      @csrf
      <div class="card-header"><h4 class="card-title">{{ __('Add contractor') }}</h4></div>
      <div class="card-body">@include('contractors._form')</div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.contractors.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
