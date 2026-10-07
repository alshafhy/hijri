@extends('layouts/contentLayoutMaster')

@section('title', __('Valuations map'))

@section('content')
<section id="valuations-map">
  <div class="row">
    <div class="col-lg-3 col-md-4">
      <div class="card">
        <div class="card-header"><h4 class="card-title mb-0">{{ __('Cities') }}</h4></div>
        <div class="list-group list-group-flush">
          <a href="{{ route('dashboard.valuation-requests.map') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $cityId === null ? 'active' : '' }}">
            <span>{{ __('All cities') }}</span>
            <span class="badge rounded-pill {{ $cityId === null ? 'bg-white text-primary' : 'bg-light-primary' }}">{{ number_format($total) }}</span>
          </a>
          @forelse ($cities as $city)
            <a href="{{ route('dashboard.valuation-requests.map', ['city' => $city['id']]) }}" class="list-group-item list-group-item-action {{ $cityId === $city['id'] ? 'active' : '' }}">
              <div class="d-flex justify-content-between align-items-center">
                <span>{{ $city['name'] }}</span>
                <span class="badge rounded-pill {{ $cityId === $city['id'] ? 'bg-white text-primary' : 'bg-light-primary' }}">{{ number_format($city['count']) }}</span>
              </div>
              <small class="{{ $cityId === $city['id'] ? '' : 'text-muted' }}">{{ __('Total value') }}: {{ number_format($city['total'], 0) }} {{ __('SAR') }}</small>
            </a>
          @empty
            <div class="list-group-item text-muted">{{ __('No valuations with a location yet.') }}</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-9 col-md-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
          <h4 class="card-title mb-0">{{ __('Valuations map') }} <span class="text-muted font-small-3">({{ number_format(count($points)) }})</span></h4>
          <a href="{{ route('dashboard.valuation-requests.index') }}" class="btn btn-sm btn-outline-primary rounded">{{ __('Back') }}</a>
        </div>
        <div class="card-body">
          @if ($truncated)
            <div class="alert alert-warning p-1">{{ __('Only the latest :count valuations are shown. Choose a city to narrow the map.', ['count' => number_format(\App\Actions\Valuation\BuildValuationMapAction::MAX_POINTS)]) }}</div>
          @endif
          <div id="valuations-map-canvas" class="rounded border" style="height: 620px;"></div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('page-script')
  <link rel="stylesheet" href="{{ asset('vendors/css/maps/leaflet.min.css') }}">
  <script src="{{ asset('vendors/js/maps/leaflet.min.js') }}"></script>
  <script>
    (function () {
      var points = {!! json_encode($points, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
      var detailsUrl = @json(route('dashboard.valuation-requests.show', '__ID__'));
      var labels = {
        reference: @json(__('Reference')),
        city: @json(__('City')),
        type: @json(__('Property type')),
        customer: @json(__('Customer')),
        owner: @json(__('Owner')),
        value: @json(__('Value')),
        sar: @json(__('SAR')),
        details: @json(__('Details'))
      };
      var map = L.map('valuations-map-canvas', { preferCanvas: true }).setView([24.7136, 46.6753], 6);
      var streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
      var aerial = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: '&copy; Esri' });
      var layers = {};
      layers[@json(__('Map'))] = streets;
      layers[@json(__('Aerial image'))] = aerial;
      L.control.layers(layers).addTo(map);

      function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
      }

      function row(label, value) {
        return value ? '<div><strong>' + escapeHtml(label) + ':</strong> ' + escapeHtml(value) + '</div>' : '';
      }

      var primary = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#7367f0';
      var bounds = [];
      points.forEach(function (p) {
        var popup = '<div class="font-small-3">'
          + row(labels.reference, p.reference)
          + row(labels.city, p.city)
          + row(labels.type, [p.kind, p.type].filter(Boolean).join(' - '))
          + row(labels.customer, p.customer)
          + row(labels.owner, p.owner)
          + row(labels.value, p.value ? Number(p.value).toLocaleString() + ' ' + labels.sar : null)
          + '<a class="btn btn-sm btn-primary mt-50" href="' + escapeHtml(detailsUrl.replace('__ID__', p.id)) + '">' + escapeHtml(labels.details) + '</a></div>';
        L.circleMarker([p.lat, p.lng], { radius: 6, color: '#fff', weight: 1, fillColor: primary, fillOpacity: 0.85 })
          .bindPopup(popup)
          .addTo(map);
        bounds.push([p.lat, p.lng]);
      });

      if (bounds.length) {
        map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
      }
    })();
  </script>
@endpush
