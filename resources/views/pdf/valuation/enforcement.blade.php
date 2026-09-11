@extends('pdf.valuation.layout')

@section('content')
    <h1>{{ __('Enforcement Center report') }}</h1>
    <p class="meta">
        {{ __('Reference') }}: {{ $reference }}
        @if ($company)
            — {{ $company->name }}
        @endif
    </p>

    <div class="section-note">
        {{ __('This report is prepared for Enforcement Center purposes and includes the core valuation summary.') }}
    </div>

    @include('pdf.valuation.partials.core-sections')

    <div class="page-break"></div>
    <h2>{{ __('Property pictures') }}</h2>
    {!! $pictures_html !!}
@endsection
