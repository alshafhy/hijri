@php
  $media = app(\App\Support\Valuation\PropertyPictureMedia::class);
  $pictures = $property->pictures->sortBy('sort_order')->values();
  $missingCount = $pictures->where('file_exists', false)->count();
@endphp
<div class="row match-height">
  <div class="col-lg-5">
    <div class="card mb-2">
      <div class="card-header"><h5 class="card-title mb-0">{{ __('Location') }}</h5></div>
      <div class="card-body">
        @include('valuation_requests.partials.map-view', ['mapId' => 'detail-map', 'lat' => $property->location?->x_axis, 'lng' => $property->location?->y_axis])
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card mb-2">
      <div class="card-header"><h5 class="card-title mb-0">{{ __('Photos') }} ({{ $pictures->count() }})</h5></div>
      <div class="card-body">
        @if ($pictures->isEmpty())
          <p class="text-muted mb-0">{{ __('No photos for this property') }}</p>
        @else
          <div class="d-flex flex-wrap gap-1">
            @foreach ($pictures as $picture)
              @php $display = $media->display($picture); @endphp
              <div class="text-center" style="width: 150px;">
                <a href="#" data-bs-toggle="modal" data-bs-target="#picture-modal-{{ $picture->id }}">
                  <img src="{{ $display['src'] }}" alt="{{ $display['label'] }}" class="img-fluid rounded border" loading="lazy" style="height: 110px; object-fit: cover; width: 100%;">
                </a>
                <small class="d-block text-truncate">{{ $picture->description ?: '—' }}</small>
                @can('deleteAttachment', $request)
                  <form method="post" action="{{ route('dashboard.valuation-requests.pictures.destroy', [$request, $picture]) }}" onsubmit="return confirm(@json(__('Are you sure?')))">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-flat-danger p-25">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </div>
              <div class="modal fade" id="picture-modal-{{ $picture->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">{{ $picture->description ?: __('Photo') }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body text-center"><img src="{{ $display['src'] }}" alt="{{ $display['label'] }}" class="img-fluid" loading="lazy"></div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          @if ($missingCount > 0)
            <p class="text-muted small mb-0 mt-1">{{ __('Missing pictures count', ['count' => $missingCount]) }}</p>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>
