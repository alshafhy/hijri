@extends('layouts/contentLayoutMaster')

@section('title', $city->name_ar)

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ $city->name_ar }} @if($city->name_en)<small class="text-muted">({{ $city->name_en }})</small>@endif</h4>
      <a href="{{ route('dashboard.geo-cities.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Back') }}</a>
    </div>
  </div>

  @can('create', App\Models\GeoNeighborhood::class)
    <div class="card mb-2">
      <div class="card-header"><h4 class="card-title">{{ __('Add neighborhood') }}</h4></div>
      <form method="POST" action="{{ route('dashboard.geo-neighborhoods.store') }}">
        @csrf
        <input type="hidden" name="city_id" value="{{ $city->id }}">
        <div class="card-body row g-2">
          <div class="col-md-5">
            <label class="form-label">{{ __('Arabic name') }}</label>
            <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="form-control @error('name_ar') is-invalid @enderror" required>
            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-5">
            <label class="form-label">{{ __('English name') }}</label>
            <input type="text" name="name_en" value="{{ old('name_en') }}" class="form-control @error('name_en') is-invalid @enderror">
            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">{{ __('Save') }}</button>
          </div>
        </div>
      </form>
    </div>
  @endcan

  <div class="card">
    <div class="card-header"><h4 class="card-title">{{ __('Neighborhoods') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Arabic name') }}</th>
            <th>{{ __('English name') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($neighborhoods as $neighborhood)
            <tr>
              <td>{{ $neighborhood->id }}</td>
              <td>{{ $neighborhood->name_ar }}</td>
              <td>{{ $neighborhood->name_en }}</td>
              <td>
                @can('delete', $neighborhood)
                  <form method="POST" action="{{ route('dashboard.geo-neighborhoods.destroy', $neighborhood) }}" onsubmit="return confirm(@json(__('Delete')))">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" type="submit">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $neighborhoods->links() }}</div>
  </div>
</section>
@endsection
