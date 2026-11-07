@php
  /** @var \App\Models\Property|null $p */
  $p = $p ?? null;
  $val = static fn (string $field, $default = null) => old($field, $p?->{$field} ?? $default);
  $number = old('number', $request->number ?? null);
@endphp
<div class="row g-1">
  <div class="col-md-4">
    <label class="form-label" for="number">{{ __('Transaction number') }}</label>
    <input type="text" name="number" id="number" class="form-control" maxlength="250" value="{{ $number }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="customer_name">{{ __('Customer name') }} <span class="text-danger">*</span></label>
    <input type="text" name="customer_name" id="customer_name" class="form-control" maxlength="128" required placeholder="{{ __('Enter customer name') }}" value="{{ $val('customer_name') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="owner_name">{{ __('Owner name') }} <span class="text-danger">*</span></label>
    <input type="text" name="owner_name" id="owner_name" class="form-control" maxlength="128" required placeholder="{{ __('Enter owner name') }}" value="{{ $val('owner_name') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="instrument_no">{{ __('Instrument number') }} <span class="text-danger">*</span></label>
    <input type="text" name="instrument_no" id="instrument_no" class="form-control" maxlength="200" required value="{{ $val('instrument_no') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="instrument_date">{{ __('Instrument date (Hijri)') }}</label>
    <input type="text" name="instrument_date" id="instrument_date" class="form-control hijri-datepicker" maxlength="64" autocomplete="off" placeholder="1446/05/20" data-exclusive-with="instrument_gregorian_date" value="{{ $val('instrument_date') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="instrument_gregorian_date">{{ __('Instrument date (Gregorian)') }}</label>
    <input type="date" name="instrument_gregorian_date" id="instrument_gregorian_date" class="form-control" data-exclusive-with="instrument_date" value="{{ $val('instrument_gregorian_date') }}">
    <small class="text-muted">{{ __('Fill either the Hijri or the Gregorian date, not both.') }}</small>
  </div>
  <div class="col-md-4 d-flex align-items-center">
    <div class="form-check mt-1">
      <input type="hidden" name="according_instrument" value="0">
      <input class="form-check-input" type="checkbox" name="according_instrument" id="according_instrument" value="1" @checked((bool) $val('according_instrument'))>
      <label class="form-check-label" for="according_instrument">{{ __('As per the deed') }}</label>
    </div>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="license_no">{{ __('License number') }}</label>
    <input type="text" name="license_no" id="license_no" class="form-control" value="{{ $val('license_no') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="license_date">{{ __('License date') }}</label>
    <input type="date" name="license_date" id="license_date" class="form-control" value="{{ $val('license_date') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="issued_by">{{ __('Issued by') }}</label>
    <input type="text" name="issued_by" id="issued_by" class="form-control" value="{{ $val('issued_by') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="commissioning_date">{{ __('Commissioning date') }}</label>
    <input type="date" name="commissioning_date" id="commissioning_date" class="form-control" value="{{ $val('commissioning_date') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="retail_no">{{ __('Subdivision minutes number') }}</label>
    <input type="text" inputmode="numeric" pattern="[0-9]*" name="retail_no" id="retail_no" class="form-control" value="{{ $val('retail_no') }}">
  </div>
  <div class="col-md-12">
    <label class="form-label" for="undergo_reasons">{{ __('Reasons for subjection') }}</label>
    <input type="text" name="undergo_reasons" id="undergo_reasons" class="form-control" maxlength="255" placeholder="{{ __('Reasons for subjection') }}" title="{{ __('Reasons the property is subject to vacant land fees') }}" value="{{ $val('undergo_reasons') }}">
  </div>
</div>
