@foreach ($static_pages as $page)
    @if (! $loop->first || ($breaks['static_pages'] ?? true))
        <pagebreak />
    @endif
    <div class="rpt-static-page"><img src="{{ $page }}" alt=""></div>
@endforeach
