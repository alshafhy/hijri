<div class="row g-2">
  <div class="col-md-4">
    <label class="form-label">{{ __('Number') }}</label>
    <input type="text" name="number" value="{{ old('number', $offer->number ?? '') }}" class="form-control @error('number') is-invalid @enderror" required>
    @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Partner') }}</label>
    <select name="partner_id" class="form-select @error('partner_id') is-invalid @enderror">
      <option value="">—</option>
      @foreach ($partners as $id => $name)
        <option value="{{ $id }}" @selected((string) old('partner_id', $offer->partner_id ?? '') === (string) $id)>{{ $name }}</option>
      @endforeach
    </select>
    @error('partner_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Partner name') }}</label>
    <input type="text" name="partner_name" value="{{ old('partner_name', $offer->partner_name ?? '') }}" class="form-control @error('partner_name') is-invalid @enderror">
    @error('partner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('City') }}</label>
    <input type="text" name="city" value="{{ old('city', $offer->city ?? '') }}" class="form-control @error('city') is-invalid @enderror">
    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Offered at') }}</label>
    <input type="date" name="offered_at" value="{{ old('offered_at', isset($offer) && $offer->offered_at ? $offer->offered_at->format('Y-m-d') : '') }}" class="form-control @error('offered_at') is-invalid @enderror">
    @error('offered_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('Valuation request') }} #</label>
    <input type="number" name="valuation_request_id" value="{{ old('valuation_request_id', $offer->valuation_request_id ?? '') }}" class="form-control @error('valuation_request_id') is-invalid @enderror">
    @error('valuation_request_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
