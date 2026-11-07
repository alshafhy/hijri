<ul class="nav navbar-nav bookmark-icons">
  @auth
    @can('viewAny', App\Models\ValuationRequest::class)
      <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ route('dashboard.valuation-requests.index') }}"
          data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Valuation requests') }}"><i class="ficon"
            data-feather="list"></i></a></li>
    @endcan
    @can('create', App\Models\ValuationRequest::class)
      <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ route('dashboard.valuation-requests.create') }}"
          data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Create valuation') }}"><i class="ficon"
            data-feather="plus-square"></i></a></li>
    @endcan
    @can('advancedSearch', App\Models\ValuationRequest::class)
      <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ route('dashboard.valuation-requests.advanced-search') }}"
          data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Advanced search') }}"><i class="ficon"
            data-feather="filter"></i></a></li>
    @endcan
    @can('valuation_request.view')
      <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ route('dashboard.valuation-requests.map') }}"
          data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Valuations map') }}"><i class="ficon"
            data-feather="map"></i></a></li>
    @endcan
  @endauth
</ul>
