@foreach ($attachment_images as $image)
    @if (! $loop->first || ($breaks['attachment_images'] ?? true))
        <pagebreak />
    @endif
    <div class="rpt-attachment-page">
        <img class="rpt-attachment-image" src="{{ $image['src'] }}" alt="{{ $image['label'] }}">
        @if ($overlay_logo)
            <img class="rpt-attachment-overlay" src="{{ $overlay_logo }}" alt="">
        @endif
    </div>
@endforeach
