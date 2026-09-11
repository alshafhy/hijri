@extends('layouts/contentLayoutMaster')

@section('title', __('Quick search'))

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header">
      <h4 class="card-title mb-0">{{ __('Quick search') }}</h4>
    </div>
    <div class="card-body">
      <form method="get" action="{{ route('dashboard.valuation-requests.quick-search') }}" class="row g-1 align-items-end">
        <div class="col-md-8">
          <label class="form-label">{{ __('Reference / number') }}</label>
          <input type="text" name="q" class="form-control" value="{{ $term }}" autofocus>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
          <a href="{{ route('dashboard.valuation-requests.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Reference') }}</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($items as $item)
            <tr>
              <td>{{ $item['id'] }}</td>
              <td>{{ $item['reference'] }}</td>
              <td>{{ $item['number'] }}</td>
              <td>{{ $item['state'] }}</td>
              <td>{{ $item['customer_name'] }}</td>
              <td>
                <a href="{{ route('dashboard.valuation-requests.show', $item['id']) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">{{ __('No results') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
