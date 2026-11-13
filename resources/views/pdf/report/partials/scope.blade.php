@if ($breaks['scope'] ?? true)
    <pagebreak />
@endif
<table class="rpt-card rpt-scope">
    <thead><tr><th colspan="2">{{ __('pdf.work_scope') }}</th></tr></thead>
    <tbody>
        @foreach ($scope as $row)
            <tr><td class="rpt-card-label">{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
        @endforeach
        @if ($cover['deposit_number'])
            <tr><td class="rpt-card-label">{{ __('pdf.deposit_number') }}</td><td class="rpt-scope-deposit">{{ $cover['deposit_number'] }}</td></tr>
        @endif
    </tbody>
</table>
