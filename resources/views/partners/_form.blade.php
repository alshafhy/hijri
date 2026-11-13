<div class="row g-2">
  <div class="col-md-6">
    <label class="form-label">{{ __('Name') }}</label>
    <input type="text" name="name" value="{{ old('name', $partner->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('Email') }}</label>
    <input type="email" name="email" value="{{ old('email', $partner->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('Phone') }}</label>
    <input type="text" name="phone_number" value="{{ old('phone_number', $partner->phone_number ?? '') }}" class="form-control @error('phone_number') is-invalid @enderror">
    @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Coordinates X') }}</label>
    <input type="text" name="x_axis" value="{{ old('x_axis', $partner->x_axis ?? '') }}" class="form-control @error('x_axis') is-invalid @enderror">
    @error('x_axis')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Coordinates Y') }}</label>
    <input type="text" name="y_axis" value="{{ old('y_axis', $partner->y_axis ?? '') }}" class="form-control @error('y_axis') is-invalid @enderror">
    @error('y_axis')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
