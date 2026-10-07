<div class="row g-2">
  <div class="col-md-6">
    <label class="form-label">{{ __('Name') }}</label>
    <input type="text" name="name" value="{{ old('name', $contractor->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('Email') }}</label>
    <input type="email" name="email" value="{{ old('email', $contractor->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('Phone') }}</label>
    <input type="text" name="phone_number" value="{{ old('phone_number', $contractor->phone_number ?? '') }}" class="form-control @error('phone_number') is-invalid @enderror">
    @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  @isset($contractor)
  <div class="col-md-3">
    <label class="form-label">{{ __('Fees') }}</label>
    <input type="number" name="fees" value="{{ old('fees', $contractor->fees) }}" class="form-control @error('fees') is-invalid @enderror" min="0">
    @error('fees')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Date from') }}</label>
    <input type="date" name="date_from" value="{{ old('date_from', optional($contractor->date_from)->format('Y-m-d')) }}" class="form-control @error('date_from') is-invalid @enderror">
    @error('date_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Date to') }}</label>
    <input type="date" name="date_to" value="{{ old('date_to', optional($contractor->date_to)->format('Y-m-d')) }}" class="form-control @error('date_to') is-invalid @enderror">
    @error('date_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Coordinates X') }}</label>
    <input type="text" name="x_axis" value="{{ old('x_axis', $contractor->x_axis) }}" class="form-control @error('x_axis') is-invalid @enderror">
    @error('x_axis')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Coordinates Y') }}</label>
    <input type="text" name="y_axis" value="{{ old('y_axis', $contractor->y_axis) }}" class="form-control @error('y_axis') is-invalid @enderror">
    @error('y_axis')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('Template') }}</label>
    <input type="number" name="template_id" value="{{ old('template_id', $contractor->template_id) }}" class="form-control @error('template_id') is-invalid @enderror" min="0">
    @error('template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  @endisset
</div>
