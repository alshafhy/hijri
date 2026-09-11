@extends('layouts/contentLayoutMaster')

@section('title', __('Not uploaded to Qima'))

@section('content')
<section>
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-header">
      <h4 class="card-title mb-0">{{ __('Not uploaded to Qima') }}</h4>
    </div>
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
          @forelse ($requests as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->reference }}</td>
              <td>{{ $item->number }}</td>
              <td>{{ $item->state }}</td>
              <td>{{ $item->property?->customer_name }}</td>
              <td class="text-nowrap">
                <a href="{{ route('dashboard.valuation-requests.show', $item) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                @can('toggleQimaStatus', $item)
                  <form method="post" action="{{ route('dashboard.valuation-requests.qima-toggle', $item) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="uploaded_on_qima" value="1">
                    <button type="submit" class="btn btn-sm btn-success">{{ __('Mark uploaded') }}</button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">{{ __('No results') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $requests->links() }}</div>
  </div>
</section>
@endsection
