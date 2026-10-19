@if ($breaks['custom_tables'] ?? false)
    <pagebreak />
@endif
@foreach ($custom_tables as $table)
    <table class="rpt-data-table rpt-custom-table">
        <thead>
            <tr><th colspan="{{ 1 + $table['columns'] }}">{{ $table['title'] }}</th></tr>
            <tr class="rpt-data-head">
                <td class="rpt-custom-field">{{ __('pdf.field_name') }}</td>
                @for ($column = 0; $column < $table['columns']; $column++)
                    <td>{{ __('pdf.the value') }}</td>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($table['rows'] as $row)
                <tr>
                    <td class="rpt-data-label">{{ $row['label'] }}</td>
                    @foreach ($row['values'] as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
