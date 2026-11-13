@extends('layouts/contentLayoutMaster')

@section('title', __('My profile'))

@section('content')
@php
  $tabs = ['info' => __('Personal info'), 'signature' => __('Signature'), 'avatar' => __('Profile picture'), 'help' => __('Help')];
  $tab = array_key_exists($tab, $tabs) ? $tab : 'info';
  $field = fn (string $name) => old($name, $user->{$name});
@endphp
<section id="my-profile">
  @include('valuation_requests.partials.flash')

  <ul class="nav nav-pills mb-2" role="tablist">
    @foreach ($tabs as $key => $label)
      <li class="nav-item">
        <button type="button" class="nav-link @if ($tab === $key) active @endif" data-bs-toggle="pill" data-bs-target="#profile-{{ $key }}" role="tab">{{ $label }}</button>
      </li>
    @endforeach
    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard.users.change-password') }}">{{ __('change-password') }}</a></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade @if ($tab === 'info') show active @endif" id="profile-info" role="tabpanel">
      <div class="card">
        <form method="POST" action="{{ route('dashboard.profile.update') }}">
          @csrf @method('PUT')
          <div class="card-body row g-1">
            @foreach ([
                'name' => [__('Name'), 'text', true],
                'email' => [__('Email'), 'email', true],
                'phone_number' => [__('Phone'), 'text', false],
                'membership_number' => [__('Membership number'), 'text', false],
                'membership_category' => [__('Membership category'), 'text', false],
                'membership_branch_type' => [__('Membership branch'), 'text', false],
            ] as $name => [$label, $type, $required])
              <div class="col-md-6">
                <label class="form-label" for="profile-{{ $name }}">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
                <input type="{{ $type }}" name="{{ $name }}" id="profile-{{ $name }}" value="{{ $field($name) }}" class="form-control @error($name) is-invalid @enderror" @required($required)>
                @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            @endforeach
          </div>
          <div class="card-footer"><button class="btn btn-primary">{{ __('Save') }}</button></div>
        </form>
      </div>
    </div>

    @foreach (['signature' => [__('Signature'), 'signature_path'], 'avatar' => [__('Profile picture'), 'avatar_path']] as $type => [$label, $column])
      <div class="tab-pane fade @if ($tab === $type) show active @endif" id="profile-{{ $type }}" role="tabpanel">
        <div class="card">
          <form method="POST" action="{{ route('dashboard.profile.images.upload', $type) }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
              @if ($user->{$column})
                <img src="{{ route('dashboard.profile.images.show', [$user, $type]) }}" alt="{{ $label }}" class="img-thumbnail mb-1" style="max-height: 160px;">
              @else
                <p class="text-muted">{{ __('No image uploaded yet.') }}</p>
              @endif
              <label class="form-label" for="image-{{ $type }}">{{ $label }}</label>
              <input type="file" name="image" id="image-{{ $type }}" accept=".jpg,.jpeg,.png,.webp" class="form-control @error('image') is-invalid @enderror" required>
              @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="card-footer"><button class="btn btn-primary">{{ __('Upload') }}</button></div>
          </form>
        </div>
      </div>
    @endforeach

    <div class="tab-pane fade @if ($tab === 'help') show active @endif" id="profile-help" role="tabpanel">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Contact support') }}</h5></div>
        <form method="POST" action="{{ route('dashboard.profile.support') }}">
          @csrf
          <div class="card-body">
            <div class="mb-1">
              <label class="form-label" for="support-title">{{ __('Subject') }}</label>
              <input type="text" name="title" id="support-title" value="{{ old('title') }}" maxlength="255" class="form-control @error('title') is-invalid @enderror" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <label class="form-label" for="support-body">{{ __('Message') }}</label>
            <textarea name="body" id="support-body" rows="5" maxlength="5000" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="card-footer"><button class="btn btn-primary">{{ __('Send') }}</button></div>
        </form>
      </div>
      @if ($supportRequests->isNotEmpty())
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('My support requests') }}</h5></div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light"><tr><th>{{ __('Subject') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created at') }}</th></tr></thead>
              <tbody>
                @foreach ($supportRequests as $supportRequest)
                  <tr>
                    <td>{{ $supportRequest->title }}</td>
                    <td><span class="badge {{ $supportRequest->status === 'open' ? 'bg-light-warning' : 'bg-light-success' }}">{{ $supportRequest->status === 'open' ? __('Open') : __('Closed') }}</span></td>
                    <td>{{ $supportRequest->created_at?->format('Y/m/d H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </div>
  </div>
</section>
@endsection
