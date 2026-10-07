@extends('layouts/auth')

@section('title', __('Activate account'))

@section('page-style')
  <link rel="stylesheet" href="{{ asset('css/base/pages/authentication.css') }}">
@endsection

@section('content')
  <div class="card">
    <div class="card-header"><b>{{ __('Activate account') }}</b></div>
    <div class="card-body">
      @if ($member === null)
        <div class="alert alert-danger p-1 mb-0">{{ __('This invitation link is invalid or has expired. Ask your manager to send a new one.') }}</div>
      @else
        <p>{{ __('Welcome :name', ['name' => $member->name]) }} — {{ $member->email }}</p>
        <p>{{ __('Your username') }}: <strong dir="ltr">{{ $member->username }}</strong></p>
        <form method="POST" action="{{ route('invitations.accept', $token) }}">
          @csrf
          <div class="mb-1">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-1">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
          </div>
          <button type="submit" class="btn btn-primary w-100">{{ __('Activate account') }}</button>
        </form>
      @endif
    </div>
  </div>
@endsection
