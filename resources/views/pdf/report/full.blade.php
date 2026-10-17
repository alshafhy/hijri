@extends('pdf.report.layout')

@section('content')
    @include('pdf.report.partials.page-header')

    @include('pdf.report.partials.cover')

    <div class="rpt-main">
        {{-- Report sections are included here, in legacy order, between the cover and the closing page. --}}
        @if ($certificate)
            @include('pdf.report.partials.certificate')
        @endif

        @include('pdf.report.partials.static-pages')

        @if ($summary)
            @include('pdf.report.partials.executive-summary')
        @endif
    </div>

    @include('pdf.report.partials.closing')
@endsection
