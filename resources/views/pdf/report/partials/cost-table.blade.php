@if ($breaks['cost_table'] ?? false)
    <pagebreak />
@endif
<table class="rpt-data-table rpt-cost-table">
    <thead>
        <tr><th colspan="4">{{ __('pdf.property_evaluation') }}</th></tr>
        <tr class="rpt-data-head">
            <td class="rpt-cost-usage">{{ __('pdf.usage') }}</td>
            <td>{{ __('pdf.space') }}</td>
            <td>{{ __('pdf.meter_price_riyals') }}</td>
            <td>{{ __('pdf.total_price_riyals') }}</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($cost_table['rows'] as $row)
            <tr>
                <td class="rpt-data-label">{{ $row['label'] }}</td>
                <td>{{ $row['area'] }}</td>
                <td>{{ $row['price'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
        @endforeach
        <tr class="rpt-cost-total">
            <td class="rpt-data-label">{{ __('pdf.estimated_cost') }}</td>
            <td colspan="2">{{ $cost_table['words'] }}</td>
            <td class="rpt-cost-amount">{{ $cost_table['amount'] }}</td>
        </tr>
    </tbody>
</table>
