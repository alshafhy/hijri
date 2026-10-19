@if ($breaks['notes'] ?? false)
    <pagebreak />
@endif
<table class="rpt-data-table rpt-notes">
    <thead>
        <tr><th colspan="2">{{ __('pdf.opinion_of_appraisal_company') }}</th></tr>
    </thead>
    <tbody>
        <tr>
            <td class="rpt-data-label">{{ __('pdf.moderator_notes') }}</td>
            <td class="rpt-notes-text">{{ $notes['coordinator'] }}</td>
        </tr>
        <tr>
            <td class="rpt-data-label">{{ __('pdf.evaluator_notes') }}</td>
            <td class="rpt-notes-text">{{ $notes['evaluator'] }}</td>
        </tr>
    </tbody>
</table>
