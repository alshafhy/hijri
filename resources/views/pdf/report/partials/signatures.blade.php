@if ($breaks['signature_block'] ?? false)
    <pagebreak />
@endif
<table class="rpt-data-table rpt-signatures">
    <thead>
        <tr><th colspan="4">{{ __('pdf.signatures') }}</th></tr>
        <tr class="rpt-data-head">
            @foreach ($signature_block['columns'] as $column)
                <td>{{ $column['title'] }}</td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach ($signature_block['columns'] as $column)
                <td class="rpt-signature-cell">
                    @foreach ($column['lines'] as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                    @if ($column['image'])
                        <img class="{{ $column['image_class'] }}" src="{{ $column['image'] }}" alt="" />
                    @endif
                </td>
            @endforeach
        </tr>
    </tbody>
</table>
@if ($signature_block['accreditation_date'])
    <p class="rpt-accreditation-date">{{ __('pdf.accreditation_date') }}: {{ $signature_block['accreditation_date'] }}</p>
@endif
