@extends('layouts/contentLayoutMaster')

@section('title', __('Offers'))

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Offers') }}</h4>
      @can('create', App\Models\Offer::class)
        <a href="{{ route('dashboard.offers.create') }}" class="btn btn-sm btn-primary">{{ __('Add offer') }}</a>
      @endcan
    </div>
    <div class="card-body">
      <form method="GET" class="row g-1 mb-2">
        <div class="col-md-3">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search') }}">
        </div>
        <div class="col-md-2">
          <input type="number" name="partner_id" value="{{ request('partner_id') }}" class="form-control" placeholder="{{ __('Partner') }} #">
        </div>
        <div class="col-md-3">
          <select name="state" class="form-select">
            <option value="">{{ __('State') }}</option>
            <option value="0" @selected(request('state') === '0')>{{ __('Waiting') }}</option>
            <option value="1" @selected(request('state') === '1')>{{ __('Rejected') }}</option>
            <option value="2" @selected(request('state') === '2')>{{ __('Accepted') }}</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-primary" type="submit">{{ __('Filter') }}</button>
        </div>
      </form>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('Partner') }}</th>
            <th>{{ __('City') }}</th>
            <th>{{ __('Offered at') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($offers as $offer)
            <tr>
              <td>{{ $offer->id }}</td>
              <td>{{ $offer->number }}</td>
              <td>{{ $offer->partner?->name ?? $offer->partner_name }}</td>
              <td>{{ $offer->city }}</td>
              <td>{{ optional($offer->offered_at)->format('Y-m-d') }}</td>
              <td>
                @if ($offer->state === App\Models\Offer::STATE_ACCEPTED)
                  <span class="badge bg-success">{{ __('Accepted') }}</span>
                @elseif ($offer->state === App\Models\Offer::STATE_REJECTED)
                  <span class="badge bg-danger">{{ __('Rejected') }}</span>
                @else
                  <span class="badge bg-secondary">{{ __('Waiting') }}</span>
                @endif
              </td>
              <td class="d-flex flex-wrap gap-1">
                <a href="{{ route('dashboard.offers.show', $offer) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                @can('update', $offer)
                  <a href="{{ route('dashboard.offers.edit', $offer) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $offers->links() }}</div>
  </div>
</section>
@endsection
