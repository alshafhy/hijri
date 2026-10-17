@extends('pdf.report.layout')

@section('content')
    @include('pdf.report.partials.page-header')

    @include('pdf.report.partials.cover')

    <div class="rpt-main">
        {{-- Report sections are included here, in legacy order, between the cover and the closing page. --}}
    </div>

    @include('pdf.report.partials.closing')
@endsection
