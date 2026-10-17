@if ($market_approach['map'])
    <pagebreak />
    <table class="rpt-map">
        <thead><tr><th>{{ __('pdf.comparison_map') }}</th></tr></thead>
        <tbody><tr><td><img class="rpt-comparables-map-image" src="{{ $market_approach['map'] }}" alt=""></td></tr></tbody>
    </table>
@endif
