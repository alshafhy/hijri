<div class="rpt-cover-page">
    <table class="rpt-cover">
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.report_number') }} :</td>
            <td class="rpt-cover-value">{{ $reference }}</td>
        </tr>
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.date_label') }} :</td>
            <td class="rpt-cover-value">
                @if ($direction === 'ltr')
                    {{ __('pdf.hijri_suffix') }} {{ $issue_date }}
                @else
                    {{ $issue_date }} {{ __('pdf.hijri_suffix') }}
                @endif
            </td>
        </tr>
        @if ($cover['deposit_number'])
            <tr>
                <td class="rpt-cover-label">{{ __('pdf.deposit_number') }} :</td>
                <td class="rpt-cover-value rpt-cover-deposit">{{ $cover['deposit_number'] }}</td>
            </tr>
        @endif
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.property_type_label_cover') }} :</td>
            <td class="rpt-cover-value">{{ $cover['property_kind'] }}</td>
        </tr>
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.usage_type_label_cover') }} :</td>
            <td class="rpt-cover-value">{{ $cover['property_type'] }} {{ __('pdf.in_prefix') }} {{ $city_name }}</td>
        </tr>
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.neighborhood_name') }} :</td>
            <td class="rpt-cover-value">{{ __('pdf.neighborhood_prefix') }} {{ $neighborhood_name }}</td>
        </tr>
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.city_name') }} :</td>
            <td class="rpt-cover-value">{{ __('pdf.city_prefix') }} {{ $city_name }}</td>
        </tr>
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.instrument_no') }} :</td>
            <td class="rpt-cover-value">{{ $cover['instrument_no'] }}</td>
        </tr>
        @if ($cover['origin_sector'])
            <tr>
                <td class="rpt-cover-label">{{ __('pdf.asset_sector_usage') }} :</td>
                <td class="rpt-cover-value">{{ $cover['origin_sector'] }}</td>
            </tr>
        @endif
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.resident_identity') }} :</td>
            <td class="rpt-cover-value">{{ $branding['name'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="rpt-cover-label">{{ __('pdf.client') }} :</td>
            <td class="rpt-cover-value">{{ $cover['customer_name'] }}</td>
        </tr>
    </table>
</div>
