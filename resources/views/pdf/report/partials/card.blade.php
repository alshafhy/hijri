<table class="rpt-card{{ ($columns ?? 2) === 4 ? ' rpt-card-paired' : '' }}{{ isset($modifier) ? ' '.$modifier : '' }}">
    <thead><tr><th colspan="{{ $columns ?? 2 }}">{{ $title }}</th></tr></thead>
    <tbody>
        @if (($columns ?? 2) === 4)
            @foreach ($lines as $line)
                <tr>
                    <td class="rpt-card-pair-label">{{ $line[0]['label'] }}</td>
                    @if (isset($line[1]))
                        <td class="rpt-card-pair-value">{{ $line[0]['value'] }}</td>
                        <td class="rpt-card-pair-label">{{ $line[1]['label'] }}</td>
                        <td class="rpt-card-pair-value">{{ $line[1]['value'] }}</td>
                    @else
                        <td colspan="3">{{ $line[0]['value'] }}</td>
                    @endif
                </tr>
            @endforeach
        @else
            @foreach ($rows as $row)
                <tr><td class="rpt-card-label">{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
            @endforeach
        @endif
    </tbody>
</table>
