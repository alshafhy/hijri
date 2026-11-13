@extends('layouts/contentLayoutMaster')

@section('title', __('Offer') . ' ' . $offer->number)

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Offer') }} {{ $offer->number }}</h4>
      <div class="d-flex gap-1 flex-wrap">
        @can('update', $offer)
          <a href="{{ route('dashboard.offers.edit', $offer) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
        @endcan
        @can('activate', $offer)
          @if ($offer->state !== App\Models\Offer::STATE_ACCEPTED)
            <form method="POST" action="{{ route('dashboard.offers.activate', $offer) }}">@csrf
              <button class="btn btn-sm btn-success" type="submit">{{ __('Activate') }}</button>
            </form>
          @endif
          @if ($offer->state !== App\Models\Offer::STATE_REJECTED)
            <form method="POST" action="{{ route('dashboard.offers.deactivate', $offer) }}">@csrf
              <button class="btn btn-sm btn-warning" type="submit">{{ __('Deactivate') }}</button>
            </form>
          @endif
        @endcan
        <a href="{{ route('dashboard.offers.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Back') }}</a>
      </div>
    </div>
    <div class="card-body row">
      <div class="col-md-4"><strong>{{ __('Partner') }}:</strong> {{ $offer->partner?->name ?? $offer->partner_name }}</div>
      <div class="col-md-4"><strong>{{ __('City') }}:</strong> {{ $offer->city }}</div>
      <div class="col-md-4"><strong>{{ __('Offered at') }}:</strong> {{ optional($offer->offered_at)->format('Y-m-d') }}</div>
      <div class="col-md-4 mt-1"><strong>{{ __('Valuation request') }}:</strong> {{ $offer->valuationRequest?->number }}</div>
      <div class="col-md-4 mt-1"><strong>{{ __('State') }}:</strong> {{ $offer->stateLabel() }}</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h4 class="card-title mb-0">{{ __('Estate lines') }}</h4></div>
    @can('update', $offer)
      @php
        $selectedKind = old('estate_kind');
        $selectedType = old('estate_type');
        $selectedCityId = old('city_id');
        if ($selectedCityId === null && filled($offer->city)) {
            $selectedCityId = optional($cities->first(fn ($city) => trim((string) $city->name_ar) === trim((string) $offer->city)))->id;
        }
        $selectedNeighborhood = old('neighborhood');
      @endphp
      <div class="card-body border-bottom">
        <form method="POST" action="{{ route('dashboard.offers.estates.store', $offer) }}" class="row g-2 align-items-end"
              data-estate-kinds='@json($kinds)'
              data-neighborhoods-by-city='@json($neighborhoodsByCity, JSON_UNESCAPED_UNICODE)'
              data-select-placeholder="{{ __('Select') }}">
          @csrf
          <div class="col-md-4">
            <label class="form-label" for="estate_kind">{{ __('Property kind') }} <span class="text-danger">*</span></label>
            <select name="estate_kind" id="estate_kind" class="form-select @error('estate_kind') is-invalid @enderror" required>
              <option value="">{{ __('Select') }}</option>
              @foreach (array_keys($kinds) as $kind)
                <option value="{{ $kind }}" @selected($selectedKind === $kind)>{{ $kind }}</option>
              @endforeach
            </select>
            @error('estate_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="estate_type">{{ __('Property type') }} <span class="text-danger">*</span></label>
            <select name="estate_type" id="estate_type" class="form-select @error('estate_type') is-invalid @enderror" required data-selected="{{ $selectedType }}">
              <option value="">{{ __('Select') }}</option>
              @foreach ($kinds[$selectedKind] ?? [] as $type)
                <option value="{{ $type }}" @selected($selectedType === $type)>{{ $type }}</option>
              @endforeach
            </select>
            @error('estate_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="instrument_no">{{ __('Instrument no') }}</label>
            <input id="instrument_no" name="instrument_no" class="form-control @error('instrument_no') is-invalid @enderror" value="{{ old('instrument_no') }}">
            @error('instrument_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="estate_area">{{ __('Area') }}</label>
            <input id="estate_area" type="number" min="0" name="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area') }}">
            @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="estate_city">{{ __('City') }}</label>
            <select id="estate_city" name="city_id" class="form-select">
              <option value="">{{ __('Select') }}</option>
              @foreach ($cities as $city)
                <option value="{{ $city->id }}" @selected((string) $selectedCityId === (string) $city->id)>{{ $city->name_ar }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="neighborhood">{{ __('Neighborhood') }}</label>
            <select id="neighborhood" name="neighborhood" class="form-select @error('neighborhood') is-invalid @enderror" data-selected="{{ $selectedNeighborhood }}">
              <option value="">{{ __('Select') }}</option>
              @foreach ($neighborhoodsByCity[$selectedCityId] ?? [] as $neighborhoodName)
                <option value="{{ $neighborhoodName }}" @selected($selectedNeighborhood === $neighborhoodName)>{{ $neighborhoodName }}</option>
              @endforeach
            </select>
            @error('neighborhood')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label" for="estate_fees">{{ __('Amount') }}</label>
            <input id="estate_fees" type="number" min="0" name="fees" class="form-control @error('fees') is-invalid @enderror" value="{{ old('fees') }}">
            @error('fees')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <button class="btn btn-primary" type="submit">{{ __('Add estate line') }}</button>
          </div>
        </form>
      </div>
    @endcan
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Property kind') }}</th>
            <th>{{ __('Property type') }}</th>
            <th>{{ __('Instrument no') }}</th>
            <th>{{ __('Area') }}</th>
            <th>{{ __('Neighborhood') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Payment') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($offer->estates as $estate)
            <tr>
              <td>{{ $estate->id }}</td>
              <td>{{ $estate->estate_kind }}</td>
              <td>{{ $estate->estate_type }}</td>
              <td>{{ $estate->instrument_no }}</td>
              <td>{{ number_format((float) ($estate->area ?? 0), 2) }}</td>
              <td>{{ $estate->neighborhood }}</td>
              <td>{{ number_format((float) ($estate->fees ?? 0), 2) }} {{ __('SAR') }}</td>
              <td>{{ $estate->payment_status->label() }}</td>
              <td>{{ $estate->status->label() }}</td>
              <td class="d-flex gap-1 flex-wrap">
                @can('update', $offer)
                  @unless ($estate->isPaid())
                    <form method="POST" action="{{ route('dashboard.offers.estates.mark-paid', [$offer, $estate]) }}">@csrf
                      <button class="btn btn-sm btn-success" type="submit">{{ __('Mark paid') }}</button>
                    </form>
                  @endunless
                  @unless ($estate->isActive())
                    <form method="POST" action="{{ route('dashboard.offers.estates.activate', [$offer, $estate]) }}">@csrf
                      <button class="btn btn-sm btn-outline-success" type="submit">{{ __('Activate') }}</button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('dashboard.offers.estates.deactivate', [$offer, $estate]) }}">@csrf
                      <button class="btn btn-sm btn-outline-warning" type="submit">{{ __('Deactivate') }}</button>
                    </form>
                  @endunless
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="10" class="text-center">{{ __('No estate lines.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection

@push('page-script')
  <script src="{{ asset('js/scripts/pages/offer-estate-form.js') }}"></script>
@endpush
