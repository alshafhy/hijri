@extends('layouts/contentLayoutMaster')

@section('title', __('Contractors'))

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Contractors') }}</h4>
      @can('create', App\Models\Contractor::class)
        <a href="{{ route('dashboard.contractors.create') }}" class="btn btn-sm btn-primary">{{ __('Add contractor') }}</a>
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
            <option value="2" @selected(request('state') === '2')>{{ __('Active') }}</option>
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
            <th>{{ __('Fees') }}</th>
            <th>{{ __('Contracts count') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($contractors as $contractor)
            <tr>
              <td>{{ $contractor->id }}</td>
              <td>{{ $contractor->name }}</td>
              <td>{{ $contractor->email }}</td>
              <td>{{ $contractor->phone_number }}</td>
              <td>{{ $contractor->fees }}</td>
              <td>{{ $contractor->contracts_count }}</td>
              <td>
                @if ($contractor->state === App\Models\Contractor::STATE_ACTIVE)
                  <span class="badge bg-success">{{ __('Active') }}</span>
                @elseif ($contractor->state === App\Models\Contractor::STATE_INACTIVE)
                  <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                @else
                  <span class="badge bg-warning">{{ __('Draft') }}</span>
                @endif
              </td>
              <td class="d-flex flex-wrap gap-1">
                <a href="{{ route('dashboard.contractors.show', $contractor) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                @can('update', $contractor)
                  <a href="{{ route('dashboard.contractors.edit', $contractor) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                @endcan
                @can('activate', $contractor)
                  @if ($contractor->state !== App\Models\Contractor::STATE_ACTIVE)
                    <form method="POST" action="{{ route('dashboard.contractors.activate', $contractor) }}">@csrf
                      <button class="btn btn-sm btn-success" type="submit">{{ __('Activate') }}</button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('dashboard.contractors.deactivate', $contractor) }}">@csrf
                      <button class="btn btn-sm btn-warning" type="submit">{{ __('Deactivate') }}</button>
                    </form>
                  @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $contractors->links() }}</div>
  </div>
</section>
@endsection
