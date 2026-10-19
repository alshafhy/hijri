@if ($breaks['final_value'] ?? false)
    <pagebreak />
@endif
<table class="rpt-data-table rpt-final-value">
    <thead>
        <tr><th colspan="2">{{ __('pdf.final_valuation_price') }}</th></tr>
    </thead>
    <tbody>
        <tr>
            <td class="rpt-final-amount">{{ $final_value['amount'] }}</td>
            <td>{{ $final_value['words'] }}</td>
        </tr>
    </tbody>
</table>
