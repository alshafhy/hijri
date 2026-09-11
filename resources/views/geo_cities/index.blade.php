@extends('layouts/contentLayoutMaster')

@section('title', __('Cities'))

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Cities') }}</h4>
      @can('create', App\Models\GeoCity::class)
        <a href="{{ route('dashboard.geo-cities.create') }}" class="btn btn-sm btn-primary">{{ __('Add city') }}</a>
      @endcan
    </div>
    <div class="card-body">
      <form method="GET" class="row g-1 mb-2">
        <div class="col-md-4">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search') }}">
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
            <th>{{ __('Arabic name') }}</th>
            <th>{{ __('English name') }}</th>
            <th>{{ __('Neighborhoods') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($cities as $city)
            <tr>
              <td>{{ $city->id }}</td>
              <td>{{ $city->name_ar }}</td>
              <td>{{ $city->name_en }}</td>
              <td>{{ $city->neighborhoods_count }}</td>
              <td class="d-flex flex-wrap gap-1">
                <a href="{{ route('dashboard.geo-cities.show', $city) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                @can('delete', $city)
                  <form method="POST" action="{{ route('dashboard.geo-cities.destroy', $city) }}" onsubmit="return confirm(@json(__('Delete')))">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" type="submit">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $cities->links() }}</div>
  </div>
</section>
@endsection
