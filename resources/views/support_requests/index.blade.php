@extends('layouts/contentLayoutMaster')

@section('title', __('Support requests'))

@section('content')
<section id="support-requests">
  @include('valuation_requests.partials.flash')
  <div class="card">
    <div class="card-header d-flex flex-wrap gap-50">
      @foreach (['' => __('All'), 'open' => __('Open'), 'closed' => __('Closed')] as $value => $label)
        <a href="{{ route('dashboard.support-requests.index', array_filter(['status' => $value])) }}" class="btn btn-sm {{ (string) $status === $value ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
      @endforeach
    </div>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>{{ __('User') }}</th><th>{{ __('Subject') }}</th><th>{{ __('Message') }}</th><th>{{ __('Created at') }}</th><th>{{ __('Status') }}</th></tr></thead>
        <tbody>
          @forelse ($supportRequests as $supportRequest)
            <tr>
              <td>{{ $supportRequest->user?->name }}<div class="small text-muted">{{ $supportRequest->user?->email }}</div></td>
              <td>{{ $supportRequest->title }}</td>
              <td style="white-space: pre-line; max-width: 420px;">{{ $supportRequest->body }}</td>
              <td class="text-nowrap">{{ $supportRequest->created_at?->format('Y/m/d H:i') }}</td>
              <td>
                @can('user.edit')
                  <form method="POST" action="{{ route('dashboard.support-requests.update', $supportRequest) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $supportRequest->status === 'open' ? 'closed' : 'open' }}">
                    <button class="btn btn-sm {{ $supportRequest->status === 'open' ? 'btn-outline-success' : 'btn-outline-secondary' }}">{{ $supportRequest->status === 'open' ? __('Mark closed') : __('Reopen') }}</button>
                  </form>
                @else
                  {{ $supportRequest->status === 'open' ? __('Open') : __('Closed') }}
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">{{ __('No support requests.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($supportRequests->hasPages())
      <div class="card-footer">{{ $supportRequests->links() }}</div>
    @endif
  </div>
</section>
@endsection
