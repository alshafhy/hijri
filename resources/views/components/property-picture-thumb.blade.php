@php
  $available = $display['available'];
  $src = $display['src'];
  $label = $display['label'];
@endphp
<figure class="property-picture-thumb mb-1 {{ $available ? '' : 'property-picture-thumb--missing' }}" style="max-width: 220px;">
  <img
    src="{{ $src }}"
    alt="{{ $available ? $label : __('Image unavailable') }}"
    class="img-fluid rounded border"
    loading="lazy"
    @if (! $available) data-missing="1" @endif
  >
  <figcaption class="small text-muted mt-25">
    {{ $label }}
    @unless ($available)
      <span class="badge bg-secondary">{{ __('Image unavailable') }}</span>
    @endunless
  </figcaption>
</figure>
