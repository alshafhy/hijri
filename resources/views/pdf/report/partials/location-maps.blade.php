@if ($breaks['location_maps'] ?? true)
    <pagebreak />
@endif
<div class="rpt-location-maps">
    @if ($map_coordinates)
        <table class="rpt-map-coordinates">
            <tr>
                <td>{{ __('pdf.location_on_map') }}</td>
                <td>{{ __('pdf.longitude') }}</td>
                <td>{{ __('pdf.latitude') }}</td>
            </tr>
            <tr>
                <td></td>
                <td>{{ $map_coordinates['longitude'] }}</td>
                <td>{{ $map_coordinates['latitude'] }}</td>
            </tr>
        </table>
    @endif
    @isset($location_maps['streets'])
        <table class="rpt-map">
            <thead><tr><th>{{ __('pdf.location_of_property') }}</th></tr></thead>
            <tbody><tr><td><img class="rpt-map-image" src="{{ $location_maps['streets'] }}" alt=""></td></tr></tbody>
        </table>
    @endisset
    @isset($location_maps['aerial'])
        <table class="rpt-map">
            <thead><tr><th>{{ __('pdf.picture_from_above') }}</th></tr></thead>
            <tbody><tr><td><img class="rpt-map-image" src="{{ $location_maps['aerial'] }}" alt=""></td></tr></tbody>
        </table>
    @endisset
</div>
