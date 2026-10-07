{{-- Read-only Leaflet map. Params: $mapId, $lat, $lng, $height (px). --}}
@php $height = $height ?? 320; @endphp
@if (is_numeric($lat ?? null) && is_numeric($lng ?? null) && (float) $lat !== 0.0 && (float) $lng !== 0.0)
  <div id="{{ $mapId }}" class="rounded border" style="height: {{ $height }}px;" data-map-view data-lat="{{ $lat }}" data-lng="{{ $lng }}"></div>
  <a class="small" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}">{{ __('Open in Google Maps') }}</a>
  @once
    @push('page-script')
      <link rel="stylesheet" href="{{ asset('vendors/css/maps/leaflet.min.css') }}">
      <script src="{{ asset('vendors/js/maps/leaflet.min.js') }}"></script>
      <script>
        document.querySelectorAll('[data-map-view]').forEach(function (el) {
          var ll = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
          var map = L.map(el, { scrollWheelZoom: false }).setView(ll, 16);
          var streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
          var aerial = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: '&copy; Esri' });
          var layers = {};
          layers[@json(__('Map'))] = streets;
          layers[@json(__('Aerial image'))] = aerial;
          L.control.layers(layers).addTo(map);
          L.marker(ll).addTo(map);
        });
      </script>
    @endpush
  @endonce
@else
  <p class="text-muted mb-0">{{ __('No location was set on the map.') }}</p>
@endif
