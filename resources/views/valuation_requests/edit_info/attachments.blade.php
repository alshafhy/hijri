@php
  $types = config('valuation_form.attachment_types');
  $canDeleteAttachment = auth()->user()?->can('deleteAttachment', $request) ?? false;
@endphp

<h5>{{ __('Attachments') }}</h5>
@if ($request->attachments->isEmpty())
  <p class="text-muted">{{ __('No attachments uploaded yet.') }}</p>
@else
  <div class="table-responsive mb-2">
    <table class="table table-sm align-middle">
      <thead class="table-light"><tr><th>{{ __('File') }}</th><th>{{ __('Type') }}</th><th>{{ __('Uploaded at') }}</th><th></th></tr></thead>
      <tbody>
        @foreach ($request->attachments as $attachment)
          <tr>
            <td>{{ $attachment->displayName() }}</td>
            <td>{{ $attachment->type ?: '—' }}</td>
            <td>{{ optional($attachment->created_at)->format('Y-m-d H:i') ?: '—' }}</td>
            <td class="text-end text-nowrap">
              <a href="{{ route('dashboard.valuation-requests.attachments.download', [$request, $attachment]) }}" class="btn btn-sm btn-outline-primary">{{ __('Download') }}</a>
              @if ($canDeleteAttachment)
                <button type="button" class="btn btn-sm btn-outline-danger" data-confirm-submit="delete-attachment-{{ $attachment->id }}">{{ __('Delete') }}</button>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

<h6>{{ __('Upload attachments') }}</h6>
<div id="attachment-rows">
  <div class="row g-1 mb-50 attachment-row">
    <div class="col-md-7"><input type="file" name="new_attachments[0][file]" class="form-control"></div>
    <div class="col-md-4">
      <select name="new_attachments[0][type]" class="form-select">
        @foreach ($types as $type)
          <option value="{{ $type }}">{{ $type }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>
<button type="button" class="btn btn-sm btn-outline-primary" id="add-attachment-row">{{ __('Add another file') }}</button>
<small class="d-block text-muted mt-50">{{ __('Allowed types') }}: {{ config('valuation_form.upload.attachment_mimes') }}</small>

@push('page-script')
  <script>
    (function () {
      var container = document.getElementById('attachment-rows');
      var next = 1;
      document.getElementById('add-attachment-row').addEventListener('click', function () {
        var row = container.querySelector('.attachment-row').cloneNode(true);
        row.querySelectorAll('input, select').forEach(function (el) {
          el.name = el.name.replace(/new_attachments\[\d+\]/, 'new_attachments[' + next + ']');
          if (el.type === 'file') { el.value = ''; }
        });
        next++;
        container.appendChild(row);
      });
    })();
  </script>
@endpush
