@extends('layouts/contentLayoutMaster')

@section('title', $partner->name)

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ $partner->name }}</h4>
      <div class="d-flex gap-1 flex-wrap">
        @can('update', $partner)
          <a href="{{ route('dashboard.partners.edit', $partner) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
        @endcan
        <a href="{{ route('dashboard.partners.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Back') }}</a>
      </div>
    </div>
    <div class="card-body row">
      <div class="col-md-4"><strong>{{ __('Email') }}:</strong> {{ $partner->email }}</div>
      <div class="col-md-4"><strong>{{ __('Phone') }}:</strong> {{ $partner->phone_number }}</div>
      <div class="col-md-4"><strong>{{ __('State') }}:</strong> {{ $partner->state }}</div>
    </div>
  </div>

  <div class="card mb-2">
    <div class="card-header"><h4 class="card-title mb-0">{{ __('Contacts') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($partner->contacts as $contact)
            <tr>
              <td>{{ $contact->name }}</td>
              <td>{{ $contact->email }}</td>
              <td>{{ $contact->phone_number }}</td>
              <td>{{ $contact->status->label() }}</td>
              <td>
                @can('update', $partner)
                  @if ($contact->isActive())
                    <form method="POST" action="{{ route('dashboard.partners.contacts.deactivate', [$partner, $contact]) }}">@csrf
                      <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Remove') }}</button>
                    </form>
                  @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ __('No contacts found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h4 class="card-title">{{ __('Offers') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('City') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($partner->offers as $offer)
            <tr>
              <td>{{ $offer->id }}</td>
              <td>{{ $offer->number }}</td>
              <td>{{ $offer->city }}</td>
              <td>{{ $offer->state }}</td>
              <td><a href="{{ route('dashboard.offers.show', $offer) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a></td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
