@if ($breaks['framework'] ?? true)
    <pagebreak />
@endif
<table class="rpt-card rpt-framework">
    <thead><tr><th colspan="2">{{ __('pdf.evaluation_framework') }}</th></tr></thead>
    <tbody>
        @foreach ($framework['groups'] as $group)
            <tr>
                <td class="rpt-card-label">{{ $group['label'] }}</td>
                <td>
                    @foreach ($group['items'] as $item)
                        <strong>{{ $item['label'] }}: </strong>{{ $item['text'] }}
                        @unless ($loop->last)
                            <br>
                        @endunless
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@if ($framework['show_standards'])
    <pagebreak />
    <table class="rpt-card rpt-standards">
        <thead><tr><th colspan="2">{{ __('pdf.valuation_standards') }}</th></tr></thead>
        <tbody>
            @foreach ($framework['standards'] as $standard)
                <tr><td class="rpt-card-label">{{ $standard['title'] }}</td><td>{{ $standard['description'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
