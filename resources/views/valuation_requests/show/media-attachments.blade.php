<div class="card mb-2">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ __('Attachments') }} ({{ $request->attachments->count() }})</h5>
    @if ($request->attachments->isNotEmpty())
      @can('downloadAttachments', $request)
        <a href="{{ route('dashboard.valuation-requests.attachments-zip', $request) }}" class="btn btn-sm btn-outline-secondary">{{ __('Download attachments') }}</a>
      @endcan
    @endif
  </div>
  @if ($request->attachments->isEmpty())
    <div class="card-body text-muted">{{ __('No attachments uploaded yet.') }}</div>
  @else
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light"><tr><th>#</th><th>{{ __('File') }}</th><th>{{ __('Type') }}</th><th>{{ __('Uploaded at') }}</th><th></th></tr></thead>
        <tbody>
          @foreach ($request->attachments as $attachment)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $attachment->displayName() }}</td>
              <td>{{ $attachment->type ?: '—' }}</td>
              <td>{{ optional($attachment->created_at)->format('Y-m-d') ?: '—' }}</td>
              <td class="text-end"><a href="{{ route('dashboard.valuation-requests.attachments.download', [$request, $attachment]) }}" class="btn btn-sm btn-outline-primary">{{ __('Download') }}</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
