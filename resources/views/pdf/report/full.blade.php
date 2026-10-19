@extends('pdf.report.layout')

@section('content')
    @include('pdf.report.partials.page-header')

    @include('pdf.report.partials.cover')

    <div class="rpt-main">
        {{-- Report sections are included here, in legacy order, between the cover and the closing page.
             Keep this order in sync with PageBreaks::SECTIONS, which decides each section's leading page break. --}}
        @if ($certificate)
            @include('pdf.report.partials.certificate')
        @endif

        @include('pdf.report.partials.static-pages')

        @if ($summary)
            @include('pdf.report.partials.executive-summary')
        @endif

        @if ($scope)
            @include('pdf.report.partials.scope')
        @endif

        @include('pdf.report.partials.framework')

        @if ($basic_data)
            @include('pdf.report.partials.basic-data')
        @endif

        @if ($property_cards)
            @include('pdf.report.partials.property-cards')
        @endif

        @if ($location_maps)
            @include('pdf.report.partials.location-maps')
        @endif

        @if ($market_approach)
            @include('pdf.report.partials.comparables')
        @endif

        @if ($cost_table)
            @include('pdf.report.partials.cost-table')
        @endif

        @if ($custom_tables)
            @include('pdf.report.partials.custom-tables')
        @endif

        @if ($final_value)
            @include('pdf.report.partials.final-value')
        @endif

        @if ($notes)
            @include('pdf.report.partials.notes')
        @endif

        @if ($signature_block)
            @include('pdf.report.partials.signatures')
        @endif

        @include('pdf.report.partials.visit-confirmation')
    </div>

    @include('pdf.report.partials.closing')
@endsection
