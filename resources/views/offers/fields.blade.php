<div class="row">
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('Number') }}</label><input name="number" class="form-control" value="{{ old('number', $offer->number ?? '') }}" required></div>
  <div class="col-md-4 mb-1">
    <label class="form-label">{{ __('Partner') }}</label>
    <select name="partner_id" class="form-select">
      <option value="">{{ __('messages.select') }}</option>
      @foreach($partners as $id => $name)
        <option value="{{ $id }}" @selected((string) old('partner_id', $offer->partner_id ?? '') === (string) $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('Partner name') }}</label><input name="partner_name" class="form-control" value="{{ old('partner_name', $offer->partner_name ?? '') }}"></div>
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('City') }}</label><input name="city" class="form-control" value="{{ old('city', $offer->city ?? '') }}"></div>
  <div class="col-md-4 mb-1"><label class="form-label">{{ __('Offered at') }}</label><input type="datetime-local" name="offered_at" class="form-control" value="{{ old('offered_at', isset($offer) && $offer->offered_at ? $offer->offered_at->format('Y-m-d\\TH:i') : '') }}"></div>
</div>
