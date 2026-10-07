@extends('layouts/contentLayoutMaster')

@section('title', __('Edit') . ' — ' . $partner->name)

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.partners.update', $partner) }}">
      @csrf
      @method('PUT')
      <div class="card-header"><h4 class="card-title">{{ __('Edit') }} — {{ $partner->name }}</h4></div>
      <div class="card-body">
        @include('partners._form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.partners.show', $partner) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
