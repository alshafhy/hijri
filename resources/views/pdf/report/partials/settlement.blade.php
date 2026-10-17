<table class="rpt-data-table rpt-settlement rpt-adjustments-cols-{{ count($market_approach['columns']) }}">
    <thead>
        <tr><th colspan="{{ $market_approach['span'] }}">{{ __('pdf.final_settlement') }}</th></tr>
        <tr class="rpt-data-head">
            <td class="rpt-adjustments-criteria"></td>
            @foreach ($market_approach['columns'] as $title)
                <td colspan="2">{{ $title }}</td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="rpt-data-label">{{ __('pdf.Price after adjustments') }}</td>
            @foreach ($market_approach['settlement']['final_meter_prices'] as $price)
                <td colspan="2">{{ $price }}</td>
            @endforeach
        </tr>
        <tr>
            <td class="rpt-data-label">{{ __('pdf.weight') }}</td>
            @foreach ($market_approach['settlement']['weights'] as $weight)
                <td colspan="2">{{ $weight }}</td>
            @endforeach
        </tr>
        <tr class="rpt-highlight">
            <td class="rpt-data-label">{{ __('pdf.meter price') }}</td>
            <td colspan="{{ $market_approach['span'] - 1 }}">{{ $market_approach['settlement']['weighted_meter_price'] }}</td>
        </tr>
        <tr class="rpt-highlight">
            <td class="rpt-data-label">{{ __('pdf.meter price approximately') }}</td>
            <td colspan="{{ $market_approach['span'] - 1 }}">{{ $market_approach['settlement']['approximate_meter_price'] }}</td>
        </tr>
    </tbody>
</table>
