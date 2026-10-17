@foreach ($static_pages as $page)
    @if ($certificate || ! $loop->first)
        <pagebreak />
    @endif
    <div class="rpt-static-page"><img src="{{ $page }}" alt=""></div>
@endforeach
