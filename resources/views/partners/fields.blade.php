<div class="row">
  <div class="col-md-6 mb-1">
    <label class="form-label">{{ __('Name') }}</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $partner->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6 mb-1">
    <label class="form-label">{{ __('Email') }}</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $partner->email ?? '') }}">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6 mb-1">
    <label class="form-label">{{ __('Phone') }}</label>
    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $partner->phone_number ?? '') }}">
  </div>
  <div class="col-md-3 mb-1">
    <label class="form-label">{{ __('X axis') }}</label>
    <input type="text" name="x_axis" class="form-control" value="{{ old('x_axis', $partner->x_axis ?? '') }}">
  </div>
  <div class="col-md-3 mb-1">
    <label class="form-label">{{ __('Y axis') }}</label>
    <input type="text" name="y_axis" class="form-control" value="{{ old('y_axis', $partner->y_axis ?? '') }}">
  </div>
</div>
