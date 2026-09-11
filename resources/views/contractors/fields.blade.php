<div class="row">
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('Name') }}</label><input name="name" class="form-control" value="{{ old('name', $contractor->name) }}" required></div>
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('Email') }}</label><input type="email" name="email" class="form-control" value="{{ old('email', $contractor->email) }}"></div>
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('Phone') }}</label><input name="phone_number" class="form-control" value="{{ old('phone_number', $contractor->phone_number) }}"></div>
  <div class="col-md-3 mb-1"><label class="form-label">{{ __('Date from') }}</label><input type="date" name="date_from" class="form-control" value="{{ old('date_from', optional($contractor->date_from)->format('Y-m-d')) }}"></div>
  <div class="col-md-3 mb-1"><label class="form-label">{{ __('Date to') }}</label><input type="date" name="date_to" class="form-control" value="{{ old('date_to', optional($contractor->date_to)->format('Y-m-d')) }}"></div>
  <div class="col-md-3 mb-1"><label class="form-label">{{ __('Fees') }}</label><input type="number" name="fees" class="form-control" value="{{ old('fees', $contractor->fees) }}"></div>
  <div class="col-md-3 mb-1"><label class="form-label">{{ __('Template') }}</label><input type="number" name="template_id" class="form-control" value="{{ old('template_id', $contractor->template_id) }}"></div>
  <div class="col-md-3 mb-1"><label class="form-label">{{ __('X axis') }}</label><input name="x_axis" class="form-control" value="{{ old('x_axis', $contractor->x_axis) }}"></div>
  <div class="col-md-3 mb-1"><label class="form-label">{{ __('Y axis') }}</label><input name="y_axis" class="form-control" value="{{ old('y_axis', $contractor->y_axis) }}"></div>
</div>
