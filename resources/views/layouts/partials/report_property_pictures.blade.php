{{-- Safe PDF fragment: missing images become text notes, never broken img tags --}}
@php
  /** @var array{images: list<array{path: string, label: string}>, missing: list<string>, notes: list<string>} $reportImages */
@endphp
<div class="report-property-pictures">
  @foreach ($reportImages['images'] as $image)
    <div style="margin-bottom: 12px;">
      <img src="{{ $image['path'] }}" alt="{{ $image['label'] }}" style="max-width: 100%; height: auto;">
      <div style="font-size: 11px; color: #666;">{{ $image['label'] }}</div>
    </div>
  @endforeach

  @foreach ($reportImages['notes'] as $note)
    <p style="font-size: 11px; color: #888; border: 1px dashed #ccc; padding: 8px;">
      {{ $note }}
    </p>
  @endforeach
</div>
