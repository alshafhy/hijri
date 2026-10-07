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
      <div class="card-body border-bottom">
        <form method="POST" action="{{ route('dashboard.offers.estates.store', $offer) }}" class="row g-1 align-items-end">
          @csrf
          <div class="col-md-2">
            <label class="form-label">{{ __('Kind') }}</label>
            <input name="estate_kind" class="form-control" value="{{ old('estate_kind') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Type') }}</label>
            <input name="estate_type" class="form-control" value="{{ old('estate_type') }}" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Instrument no') }}</label>
            <input name="instrument_no" class="form-control" value="{{ old('instrument_no') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">{{ __('Area') }}</label>
            <input type="number" min="0" name="area" class="form-control" value="{{ old('area') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Neighborhood') }}</label>
            <input name="neighborhood" class="form-control" value="{{ old('neighborhood') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">{{ __('Fees') }}</label>
            <input type="number" min="0" name="fees" class="form-control" value="{{ old('fees') }}">
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">{{ __('Add estate line') }}</button>
          </div>
        </form>
      </div>
    @endcan
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Kind') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Instrument no') }}</th>
            <th>{{ __('Area') }}</th>
            <th>{{ __('Neighborhood') }}</th>
            <th>{{ __('Fees') }}</th>
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
