<table class="rpt-card{{ isset($modifier) ? ' '.$modifier : '' }}">
    <thead><tr><th colspan="2">{{ $title }}</th></tr></thead>
    <tbody>
        @foreach ($rows as $row)
            <tr><td class="rpt-card-label">{{ $row['label'] }}</td><td>{{ $row['value'] }}</td></tr>
        @endforeach
    </tbody>
</table>
