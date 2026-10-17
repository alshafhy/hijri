@if ($breaks['certificate'] ?? false)
    <pagebreak />
@endif
<div class="rpt-certificate">
    <h1 class="rpt-certificate-title">{{ __('pdf.real_estate_appraisal_certificate') }}</h1>
    <table class="rpt-certificate-table">
        <tr>
            <td class="rpt-certificate-label"><h3 class="rpt-certificate-heading">{{ __('pdf.work_scope') }}</h3></td>
            <td class="rpt-certificate-scope">
                {{ __('pdf.real_estate_appraisal') }}: {{ $cover['property_kind'] }} - {{ $cover['property_type'] }}
                <br><br>
                {{ __('pdf.city_name') }}: {{ $city_name }} - {{ $neighborhood_name }}
                <br><br>
                {{ __('pdf.commissioned_by') }}: {{ $cover['customer_name'] }}
                <br><br>
                {{ __('pdf.owner_name') }}: {{ $certificate['owner_name'] }}
                @if ($cover['instrument_no'])
                    <br><br>
                    {{ __('pdf.instrument_no') }}: {{ $cover['instrument_no'] }}
                @endif
            </td>
        </tr>
        @foreach ($certificate['rows'] as $row)
            <tr>
                <td class="rpt-certificate-label"><h3 class="rpt-certificate-heading">{{ $row['label'] }}</h3></td>
                <td class="rpt-certificate-value">{{ $row['value'] }}</td>
            </tr>
        @endforeach
    </table>

    <pagebreak />

    <table class="rpt-certificate-table">
        <tr>
            <td class="rpt-certificate-label" colspan="2"><h3 class="rpt-certificate-heading">{{ __('pdf.assessment_method_information') }}</h3></td>
        </tr>
        @foreach ($certificate['approaches'] as $approach)
            <tr>
                <td class="rpt-certificate-label"><h4 class="rpt-certificate-heading">- {{ $approach['label'] }}</h4></td>
                <td class="rpt-certificate-value">{{ $approach['value'] }}</td>
            </tr>
        @endforeach
        @if ($certificate['approach_note'])
            <tr>
                <td class="rpt-certificate-label"><h3 class="rpt-certificate-heading">{{ __('pdf.method_of_extracting_value_label') }}</h3></td>
                <td class="rpt-certificate-value">{{ $certificate['approach_note'] }}</td>
            </tr>
        @endif
    </table>

    <p class="rpt-certificate-disclaimer">{{ __('pdf.certification_and_disclaimer_des', ['company' => $branding['name'] ?? '']) }}</p>

    <p class="rpt-certificate-final">
        {{ __('pdf.based_on_studies', ['company' => $branding['name'] ?? '']) }}
        <br>
        <span class="rpt-certificate-final-label">{{ __('pdf.final_valuation_price_label') }}</span>
        {{ __('pdf.estimated_at') }} ({{ $final_amount }} {{ __('pdf.saudi_riyal') }}) <br>({{ $value_in_words }})
        <br>
        <span class="rpt-certificate-final-basis">{{ __('pdf.based_on_application') }}</span>
    </p>

    @if ($branding)
        <p class="rpt-certificate-company">
            <strong>{{ $branding['name'] }}</strong><br>
            <strong>{{ __('pdf.evaluation_and_studies_department') }}@if ($branding['address']): {{ $branding['address'] }}@endif</strong>
            @foreach ($certificate['company_lines'] as $line)
                <br>
                @foreach ($line['parts'] as $part)
                    @unless ($loop->first){{ $line['separator'] }}@endunless<strong>{{ $part['label'] }}</strong>: {{ $part['value'] }}
                @endforeach
            @endforeach
        </p>
    @endif
</div>
