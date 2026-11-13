@if ($breaks['basic_data'] ?? true)
    <pagebreak />
@endif
@include('pdf.report.partials.card', $basic_data + ['modifier' => 'rpt-basic-data'])
