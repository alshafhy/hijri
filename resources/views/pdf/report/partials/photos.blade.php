@if ($breaks['report_images'] ?? true)
    <pagebreak />
@endif
<div class="rpt-photos">
    <table class="rpt-data-table">
        <thead>
            <tr><th colspan="2">{{ __('pdf.pictures_of_property') }}</th></tr>
        </thead>
        <tbody>
            @if (count($report_images['images']) === 0 && count($report_images['notes']) === 0)
                <tr><td class="rpt-photos-empty">{{ __('pdf.no_image_uploaded') }}</td></tr>
            @else
                @foreach (array_chunk($report_images['images'], 2) as $row)
                    <tr>
                        @foreach ($row as $image)
                            <td class="rpt-photo-cell">
                                <img class="rpt-photo-image" src="{{ $image['path'] }}" alt="{{ $image['label'] }}">
                                @if ($overlay_logo)
                                    <img class="rpt-photo-overlay" src="{{ $overlay_logo }}" alt="">
                                @endif
                                @if (! in_array($image['label'], ['لا يوجد', 'None'], true))
                                    <p class="rpt-photo-caption">{{ __('pdf.image_description') }}: {{ $image['label'] }}</p>
                                @endif
                            </td>
                        @endforeach
                        @if (count($row) === 1)
                            <td class="rpt-photo-cell"></td>
                        @endif
                    </tr>
                @endforeach
                @foreach ($report_images['notes'] as $note)
                    <tr><td class="rpt-photo-missing" colspan="2">{{ $note }}</td></tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
