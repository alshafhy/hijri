@extends('layouts/contentLayoutMaster')

@section('title', __('Edit') . ' — ' . $contractor->name)

@section('content')
<section>
  <div class="card">
    <form method="POST" action="{{ route('dashboard.contractors.update', $contractor) }}">
      @csrf
      @method('PUT')
      <div class="card-header"><h4 class="card-title">{{ __('Edit') }} — {{ $contractor->name }}</h4></div>
      <div class="card-body">@include('contractors._form')</div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('dashboard.contractors.show', $contractor) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</section>
@endsection
