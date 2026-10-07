@extends('layouts/contentLayoutMaster')

@section('title', __('Deleted valuations'))

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title mb-0">{{ __('Deleted valuations') }}</h4>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Reference') }}</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Deleted at') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($requests as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->reference }}</td>
              <td>{{ $item->number }}</td>
              <td>{{ $item->stateLabel() }}</td>
              <td>{{ optional($item->deleted_at)->format('Y-m-d H:i') }}</td>
              <td>
                @can('restore', $item)
                  <form method="post" action="{{ route('dashboard.valuation-requests.restore', $item->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">{{ __('Restore') }}</button>
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
