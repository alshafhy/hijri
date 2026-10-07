@extends('pdf.valuation.layout')

@section('content')
    <h1>{{ $report_title }}</h1>
    <p class="meta">
        {{ __('Reference') }}: {{ $reference }}
        @if ($company)
            — {{ $company->name }}
        @endif
    </p>

    <h2>{{ __('Executive summary') }}</h2>
    <table class="report">
        <tr>
            <td class="label">{{ __('Valuation type') }}</td>
            <td>{{ $property?->valuation_type_desc ?: ($property?->valuation_type ?: '—') }}</td>
            <td class="label">{{ __('Valuation usage') }}</td>
            <td>{{ $property?->valuation_usage_desc ?: ($property?->valuation_usage ?: '—') }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Evaluation date') }}</td>
            <td>{{ $property?->evaluation_date?->format('d-m-Y') ?: '—' }}</td>
            <td class="label">{{ __('Assumptions') }}</td>
            <td>{{ $property?->assumptions ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Area of search') }}</td>
            <td>{{ $property?->area_of_search ?: '—' }}</td>
            <td class="label">{{ __('Area approval way') }}</td>
            <td>{{ $property?->area_approval_way ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Requested papers') }}</td>
            <td colspan="3">{{ $property?->requested_papers ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Conclusion') }}</td>
            <td colspan="3">{{ $property?->conclusion_value ?: '—' }}</td>
        </tr>
    </table>

    @if ($land)
    <h2>{{ __('Land and finishing details') }}</h2>
    <table class="report">
        <tr>
            <td class="label">{{ __('Land nature') }}</td>
            <td>{{ $land->land_nature ?: '—' }}</td>
            <td class="label">{{ __('Facade') }}</td>
            <td>{{ $land->facade ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Ownership type') }}</td>
            <td>{{ $land->ownership_type ?: '—' }}</td>
            <td class="label">{{ __('Finishing status') }}</td>
            <td>{{ $land->finishing_status ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Street width') }}</td>
            <td>{{ $land->street_width !== null ? $land->street_width : '—' }}</td>
            <td class="label">{{ __('Total building area') }}</td>
            <td>{{ $land->total_building_area !== null ? $land->total_building_area : '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Surrounding facilities') }}</td>
            <td colspan="3">{{ $land->surrounding_facilities ?: '—' }}</td>
        </tr>
    </table>
    @endif

    @include('pdf.valuation.partials.core-sections')

    @if (! $hide_comparisons && $comparables->isNotEmpty())
        <div class="page-break"></div>
        <h2>{{ __('Comparables') }}</h2>
        <table class="report">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Area') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Percentage') }}</th>
                    <th>{{ __('Source') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($comparables as $comparable)
                    <tr>
                        <td style="text-align:center;">{{ $comparable->sequence }}</td>
                        <td>{{ $comparable->real_estate_type ?: '—' }}</td>
                        <td style="text-align:center;">{{ $comparable->area !== null ? number_format((float) $comparable->area, 2) : '—' }}</td>
                        <td style="text-align:center;">{{ $comparable->price !== null ? number_format((float) $comparable->price, 2) : '—' }}</td>
                        <td style="text-align:center;">{{ $comparable->percentage !== null ? $comparable->percentage.'%' : '—' }}</td>
                        <td>{{ $comparable->information_source ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($adjustments->isNotEmpty())
        <div class="page-break"></div>
        <h2>{{ __('Adjustments') }}</h2>
        <table class="report">
            <thead>
                <tr>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Meter price') }}</th>
                    <th>{{ __('Total adjustments') }}</th>
                    <th>{{ __('Settlement ratio') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adjustments as $adjustment)
                    <tr>
                        <td style="text-align:center;">{{ $adjustment->type }}</td>
                        <td style="text-align:center;">{{ $adjustment->meter_price !== null ? number_format((float) $adjustment->meter_price, 2) : '—' }}</td>
                        <td style="text-align:center;">{{ $adjustment->total_adjustments_price !== null ? number_format((float) $adjustment->total_adjustments_price, 2) : '—' }}</td>
                        <td style="text-align:center;">{{ $adjustment->settlement_ratio !== null ? number_format((float) $adjustment->settlement_ratio, 2) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($property?->notes || $property?->notes_property || $property?->notes_real_estate)
        <h2>{{ __('Notes') }}</h2>
        <div class="section-note">
            {{ $property->notes_real_estate }}
            {{ $property->notes_property }}
            {{ $property->notes }}
        </div>
    @endif

    <div class="page-break"></div>
    <h2>{{ __('Property pictures') }}</h2>
    {!! $pictures_html !!}
@endsection
