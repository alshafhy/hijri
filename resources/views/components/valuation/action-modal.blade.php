@props(['id', 'title', 'action', 'method' => 'POST', 'submit' => null, 'submitClass' => 'btn-primary', 'files' => false])
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="{{ $action }}" class="modal-content" @if ($files) enctype="multipart/form-data" @endif>
      @csrf
      @if (strtoupper($method) !== 'POST')
        @method($method)
      @endif
      <div class="modal-header">
        <h5 class="modal-title" id="{{ $id }}-title">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <div class="modal-body">
        {{ $slot }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn {{ $submitClass }}">{{ $submit ?? __('Confirm') }}</button>
      </div>
    </form>
  </div>
</div>
