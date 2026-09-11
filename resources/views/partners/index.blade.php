@extends('layouts/contentLayoutMaster')

@section('title', __('Partners'))

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Partners') }}</h4>
      @can('create', App\Models\Partner::class)
        <a href="{{ route('dashboard.partners.create') }}" class="btn btn-sm btn-primary">{{ __('Add partner') }}</a>
      @endcan
    </div>
    <div class="card-body">
      <form method="GET" class="row g-1 mb-2">
        <div class="col-md-4">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search') }}">
        </div>
        <div class="col-md-3">
          <select name="state" class="form-select">
            <option value="">{{ __('State') }}</option>
            <option value="1" @selected(request('state') === '1')>{{ __('Active') }}</option>
            <option value="0" @selected(request('state') === '0')>{{ __('Draft') }}</option>
            <option value="-1" @selected(request('state') === '-1')>{{ __('Inactive') }}</option>
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
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Offers count') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($partners as $partner)
            <tr>
              <td>{{ $partner->id }}</td>
              <td>{{ $partner->name }}</td>
              <td>{{ $partner->email }}</td>
              <td>{{ $partner->phone_number }}</td>
              <td>{{ $partner->offers_count }}</td>
              <td>
                @if ($partner->state === App\Models\Partner::STATE_ACTIVE)
                  <span class="badge bg-success">{{ __('Active') }}</span>
                @elseif ($partner->state === App\Models\Partner::STATE_INACTIVE)
                  <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                @else
                  <span class="badge bg-warning">{{ __('Draft') }}</span>
                @endif
              </td>
              <td class="d-flex flex-wrap gap-1">
                <a href="{{ route('dashboard.partners.show', $partner) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                @can('update', $partner)
                  <a href="{{ route('dashboard.partners.edit', $partner) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                @endcan
                @can('activate', $partner)
                  @if ($partner->state !== App\Models\Partner::STATE_ACTIVE)
                    <form method="POST" action="{{ route('dashboard.partners.activate', $partner) }}">@csrf
                      <button class="btn btn-sm btn-success" type="submit">{{ __('Activate') }}</button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('dashboard.partners.deactivate', $partner) }}">@csrf
                      <button class="btn btn-sm btn-warning" type="submit">{{ __('Deactivate') }}</button>
                    </form>
                  @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $partners->links() }}</div>
  </div>
</section>
@endsection
