{{-- Leaflet location picker. x_axis = latitude, y_axis = longitude (legacy convention). --}}
@php
  $mapId = $mapId ?? 'valuation-map';
  $latValue = $lat ?? null;
  $lngValue = $lng ?? null;
@endphp
<div class="row g-1 mb-1">
  <div class="col-md-4">
    <label class="form-label" for="{{ $mapId }}-lat">{{ __('Latitude') }} (X)</label>
    <input type="text" class="form-control" id="{{ $mapId }}-lat" name="{{ $latName }}" value="{{ $latValue }}">
  </div>
  <div class="col-md-4">
    <label class="form-label" for="{{ $mapId }}-lng">{{ __('Longitude') }} (Y)</label>
    <input type="text" class="form-control" id="{{ $mapId }}-lng" name="{{ $lngName }}" value="{{ $lngValue }}">
  </div>
  <div class="col-md-4 d-flex align-items-end gap-50">
    <button type="button" class="btn btn-outline-primary" data-map-locate="{{ $mapId }}">{{ __('My current location') }}</button>
  </div>
</div>
<div id="{{ $mapId }}" class="rounded border" style="height: 380px;"></div>
<small class="text-muted">{{ __('Click the map or drag the marker to set the property location.') }}</small>

@once
  @push('page-script')
    <link rel="stylesheet" href="{{ asset('vendors/css/maps/leaflet.min.css') }}">
    <script src="{{ asset('vendors/js/maps/leaflet.min.js') }}"></script>
    <script>
      window.initValuationMap = function (mapId) {
        var el = document.getElementById(mapId);
        if (!el || el.dataset.ready) { return; }
        el.dataset.ready = '1';
        var latInput = document.getElementById(mapId + '-lat');
        var lngInput = document.getElementById(mapId + '-lng');
        var lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
        var has = !isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0;
        var map = L.map(el).setView(has ? [lat, lng] : [24.7136, 46.6753], has ? 15 : 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19, attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        var marker = L.marker(has ? [lat, lng] : map.getCenter(), { draggable: true }).addTo(map);
        function setPos(ll) {
          marker.setLatLng(ll);
          latInput.value = ll.lat.toFixed(10);
          lngInput.value = ll.lng.toFixed(10);
        }
        marker.on('dragend', function () { setPos(marker.getLatLng()); });
        map.on('click', function (e) { setPos(e.latlng); });
        function syncFromInputs() {
          var a = parseFloat(latInput.value), b = parseFloat(lngInput.value);
          if (!isNaN(a) && !isNaN(b)) { marker.setLatLng([a, b]); map.setView([a, b]); }
        }
        latInput.addEventListener('change', syncFromInputs);
        lngInput.addEventListener('change', syncFromInputs);
        document.querySelectorAll('[data-map-locate="' + mapId + '"]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            if (!navigator.geolocation) { return; }
            navigator.geolocation.getCurrentPosition(function (p) {
              var ll = L.latLng(p.coords.latitude, p.coords.longitude);
              setPos(ll); map.setView(ll, 16);
            });
          });
        });
        el._leafletMap = map;
        setTimeout(function () { map.invalidateSize(); }, 200);
      };
      document.addEventListener('shown.bs.tab', function () {
        document.querySelectorAll('[data-valuation-map]').forEach(function (el) {
          window.initValuationMap(el.id);
          if (el._leafletMap) { el._leafletMap.invalidateSize(); }
        });
      });
    </script>
  @endpush
@endonce
@push('page-script')
  <script>
    document.getElementById(@json($mapId)).setAttribute('data-valuation-map', '1');
    if (document.getElementById(@json($mapId)).offsetParent !== null) {
      window.initValuationMap(@json($mapId));
    }
  </script>
@endpush
