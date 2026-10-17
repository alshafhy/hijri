<pagebreak />
<table class="rpt-data-table rpt-adjustments rpt-adjustments-cols-{{ count($market_approach['columns']) }}">
    <thead>
        <tr><th colspan="{{ $market_approach['span'] }}">{{ __('pdf.Settlements table') }}</th></tr>
        <tr class="rpt-data-head">
            <td class="rpt-adjustments-criteria"></td>
            @foreach ($market_approach['columns'] as $title)
                <td colspan="2" class="rpt-adjustments-comparator">{{ $title }}</td>
            @endforeach
        </tr>
        <tr class="rpt-data-subhead">
            <td>{{ __('pdf.Criteria') }}</td>
            @foreach ($market_approach['columns'] as $title)
                <td>{{ __('pdf.the value') }}</td>
                <td>{{ __('pdf.settlement ratio') }}</td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($market_approach['rows'] as $row)
            <tr>
                <td class="rpt-data-label">{{ $row['label'] }}</td>
                @foreach ($row['values'] as $index => $value)
                    @if ($row['merged'])
                        <td colspan="2">{{ $value }}</td>
                    @else
                        <td>{{ $value }}</td>
                        <td>{{ $row['percentages'][$index] }}</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
