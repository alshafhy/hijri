@extends('layouts/contentLayoutMaster')

@section('title', __('Neighborhoods'))

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Neighborhoods') }}</h4>
      <a href="{{ route('dashboard.geo-cities.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Cities') }}</a>
    </div>
    <div class="card-body">
      <form method="GET" class="row g-1">
        <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
        <input type="hidden" name="dir" value="{{ $dir ?? request('dir', 'desc') }}">
        <div class="col-md-4">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search') }}">
        </div>
        <div class="col-md-4">
          <select name="city_id" class="form-select">
            <option value="">{{ __('All cities') }}</option>
            @foreach ($cities as $id => $name)
              <option value="{{ $id }}" @selected((string) request('city_id') === (string) $id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-primary w-100" type="submit">{{ __('Filter') }}</button>
        </div>
      </form>
    </div>
  </div>

  @can('create', App\Models\GeoNeighborhood::class)
    <div class="card mb-2">
      <div class="card-header"><h5 class="card-title mb-0">{{ __('Add neighborhood') }}</h5></div>
      <form method="POST" action="{{ route('dashboard.geo-neighborhoods.store') }}">
        @csrf
        <input type="hidden" name="return_to_index" value="1">
        <div class="card-body row g-1">
          <div class="col-md-4">
            <select name="city_id" class="form-select" required>
              <option value="">{{ __('City') }}</option>
              @foreach ($cities as $id => $name)
                <option value="{{ $id }}" @selected((string) old('city_id', request('city_id')) === (string) $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="name_ar" class="form-control" placeholder="{{ __('Name (AR)') }}" value="{{ old('name_ar') }}" required>
          </div>
          <div class="col-md-3">
            <input type="text" name="name_en" class="form-control" placeholder="{{ __('Name (EN)') }}" value="{{ old('name_en') }}">
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">{{ __('Save') }}</button>
          </div>
        </div>
      </form>
    </div>
  @endcan

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>@include('components.sortable-th', ['column' => 'id', 'label' => '#'])</th>
            <th>@include('components.sortable-th', ['column' => 'name_ar', 'label' => __('Name (AR)')])</th>
            <th>@include('components.sortable-th', ['column' => 'name_en', 'label' => __('Name (EN)')])</th>
            <th>@include('components.sortable-th', ['column' => 'city_id', 'label' => __('City')])</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($neighborhoods as $neighborhood)
            <tr>
              <td>{{ $neighborhood->id }}</td>
              <td>{{ $neighborhood->name_ar }}</td>
              <td>{{ $neighborhood->name_en }}</td>
              <td>{{ $neighborhood->city?->name_ar }}</td>
              <td>
                @can('delete', $neighborhood)
                  <form method="POST" action="{{ route('dashboard.geo-neighborhoods.destroy', $neighborhood) }}" onsubmit="return confirm(@json(__('Are you sure?')))">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">{{ __('No results') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $neighborhoods->links() }}</div>
  </div>
</section>
@endsection
