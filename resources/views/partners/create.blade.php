@extends('layouts/contentLayoutMaster')

@section('title', __('Add partner'))

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.partners.store') }}">
      @csrf
      <div class="card-header"><h4 class="card-title">{{ __('Add partner') }}</h4></div>
      <div class="card-body">
        @include('partners._form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.partners.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
