@if ($breaks['market_approach'] ?? true)
    <pagebreak />
@endif
<div class="rpt-market-approach">
    <table class="rpt-data-table rpt-comparables">
        <thead>
            <tr><th colspan="6">{{ __('pdf.comparative_real_estate') }}</th></tr>
            <tr class="rpt-data-head">
                <td></td>
                <td>{{ __('pdf.comparative_description') }}</td>
                <td>{{ __('pdf.interfaces_type') }}</td>
                <td>{{ __('pdf.space') }}</td>
                <td>{{ __('pdf.price_per_meter') }}</td>
                <td>{{ __('pdf.total_price') }}</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($market_approach['comparables'] as $comparable)
                <tr>
                    <td class="rpt-data-label">{{ $comparable['label'] }}</td>
                    <td>{{ $comparable['type'] }}</td>
                    <td>{{ $comparable['front_type'] }}</td>
                    <td>{{ $comparable['area'] }}</td>
                    <td>{{ $comparable['price_per_meter'] }}</td>
                    <td>{{ $comparable['total'] }}</td>
                </tr>
            @endforeach
            @foreach ($market_approach['comparables'] as $comparable)
                @if ($comparable['coordinates_url'])
                    <tr>
                        <td class="rpt-data-label">{{ $comparable['coordinates_label'] }}</td>
                        <td colspan="5"><a class="rpt-link" href="{{ $comparable['coordinates_url'] }}">{{ __('pdf.open_coordinates') }}</a></td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td class="rpt-data-source" colspan="6"><strong>{{ __('pdf.information_source') }}</strong>: {{ $market_approach['information_source'] }}</td>
            </tr>
        </tbody>
    </table>

    @include('pdf.report.partials.comparables-map')

    @if ($market_approach['columns'] !== [])
        @include('pdf.report.partials.adjustments')
        @include('pdf.report.partials.settlement')
    @endif
</div>
