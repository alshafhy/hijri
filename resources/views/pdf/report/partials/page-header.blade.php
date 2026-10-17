<htmlpageheader name="page-header">
    <table class="rpt-header">
        <tr>
            <td class="rpt-header-title">{{ __('pdf.real_estate_appraisal') }}</td>
            <td class="rpt-header-logo" rowspan="4">
                @if ($branding['report_logo'] ?? null)
                    <img src="{{ $branding['report_logo'] }}" alt="logo">
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{ __('pdf.evaluation_subject') }}</strong>:
                {{ $cover['property_kind'] }} {{ $cover['property_type'] }}
                <strong>{{ __('pdf.in_prefix') }}</strong>
                <strong>{{ __('pdf.city_prefix') }}</strong> {{ $city_name }}
                - <strong>{{ __('pdf.neighborhood_prefix') }}</strong> {{ $neighborhood_name }}
            </td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.evaluation_for') }}</strong>: {{ $cover['customer_name'] }}</td>
        </tr>
        <tr>
            <td>
                <strong>{{ __('pdf.report_creation_date') }}</strong>: {{ $issue_date }},
                <strong>{{ __('pdf.report_type') }}</strong>: {{ __('pdf.التقرير الكامل') }},
                <strong>{{ __('pdf.report_reference_number') }}</strong>: {{ $reference }}
            </td>
        </tr>
    </table>
    <hr class="rpt-header-rule">
</htmlpageheader>

<htmlpagefooter name="page-footer">
    <table class="rpt-footer">
        <tr>
            <td class="rpt-footer-text">
                {{ $branding['name'] ?? '' }} - {{ __('pdf.Evaluation and Studies Department') }}: {{ $branding['address'] ?? '' }}
                - {{ __('pdf.Membership of the Saudi Authority for Residents - accredited') }}: {{ $branding['membership_number'] ?? '' }}
                - {{ __('pdf.company reg number') }}: {{ $branding['reg_number'] ?? '' }}
            </td>
            <td class="rpt-footer-page">{PAGENO}/{nbpg}</td>
        </tr>
    </table>
</htmlpagefooter>
