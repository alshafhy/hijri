@if ($breaks['summary'] ?? true)
    <pagebreak />
@endif
<table class="rpt-card rpt-executive-summary">
    <thead><tr><th colspan="2">{{ __('pdf.executive_summary') }}</th></tr></thead>
    <tbody>
        @foreach ($summary['intro'] as $row)
            <tr><td class="rpt-card-label">{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
        @endforeach
        <tr>
            <td class="rpt-card-label">{{ __('pdf.preparation_team') }}</td>
            <td>
                <table class="rpt-summary-team">
                    <tr>
                        <td class="rpt-summary-team-label">{{ __('pdf.property_evaluator') }}</td>
                        <td class="rpt-summary-team-name">{{ $summary['team']['evaluator_name'] }}</td>
                        <td class="rpt-summary-team-label">{{ __('pdf.effective') }}</td>
                        <td>{{ $summary['team']['evaluator_membership'] }}</td>
                    </tr>
                    <tr>
                        <td class="rpt-summary-team-label">{{ __('pdf.evaluation_company') }}</td>
                        <td class="rpt-summary-team-name">{{ $summary['team']['company_name'] }}</td>
                        <td class="rpt-summary-team-label">{{ __('pdf.effective') }}</td>
                        <td>{{ $summary['team']['approver_membership'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        @foreach ($summary['rows'] as $row)
            <tr><td class="rpt-card-label">{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
        @endforeach
    </tbody>
</table>

<pagebreak />

<table class="rpt-card rpt-executive-summary">
    <thead><tr><th colspan="2">{{ __('pdf.executive_summary') }}</th></tr></thead>
    <tbody>
        @foreach ($summary['continued'] as $row)
            <tr><td class="rpt-card-label">{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
        @endforeach
    </tbody>
</table>
