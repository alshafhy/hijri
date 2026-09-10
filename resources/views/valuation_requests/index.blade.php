@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation requests'))

@section('content')
<section>
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">{{ __('Valuation requests') }}</h4>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Number') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Qima') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($requests as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->number }}</td>
              <td>{{ $item->state }}</td>
              <td>
                @if ($item->uploaded_on_qima)
                  <span class="badge bg-success">{{ __('Uploaded to Qima') }}</span>
                @else
                  <span class="badge bg-secondary">—</span>
                @endif
              </td>
              <td>
                <a href="{{ route('dashboard.valuation-requests.show', $item) }}" class="btn btn-sm btn-primary">
                  {{ __('View') }}
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $requests->links() }}</div>
  </div>
</section>
@endsection
