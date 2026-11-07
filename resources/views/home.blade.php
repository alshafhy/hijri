@extends('layouts/contentLayoutMaster')

@section('title', __('dashboard.title'))

@section('page-style')
  <link rel="stylesheet" href="{{ asset(($dir ?? 'rtl') === 'rtl' ? 'css-rtl/base/pages/dashboard-ecommerce.rtl.css' : 'css/base/pages/dashboard-ecommerce.css') }}">
@endsection

@section('vendor-script')
  <script src="{{ asset('vendors/js/charts/apexcharts.min.js') }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset('js/scripts/pages/dashboard-home.js') }}"></script>
@endsection

@section('content')
<section class="dashboard-ecommerce">
  <div class="row mb-2">
    <div class="col-12">
      <h2 class="mb-25">{{ __('dashboard.title') }}</h2>
      <p class="text-muted mb-0">{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</p>
    </div>
  </div>

  <div class="row match-height">
    <div class="col-lg-4 col-sm-6 col-12">
      <div class="card card-statistics">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div class="truncate">
              <h2 class="fw-bolder mb-25">{{ number_format($widgets['approved_count']) }}</h2>
              <p class="card-text mb-0">{{ __('Approved valuations') }}</p>
            </div>
            <span class="avatar bg-light-primary p-50">
              <span class="avatar-content">
                <i data-feather="check-circle" class="font-medium-4"></i>
              </span>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
      <div class="card card-statistics">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div class="truncate">
              <h2 class="fw-bolder mb-25">{{ number_format((float) $widgets['total_valued_area'], 0) }}</h2>
              <p class="card-text mb-0">{{ __('Total valued area') }} <span class="text-muted">({{ __('m²') }})</span></p>
            </div>
            <span class="avatar bg-light-info p-50">
              <span class="avatar-content">
                <i data-feather="layers" class="font-medium-4"></i>
              </span>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-sm-6 col-12">
      <div class="card card-statistics">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div class="truncate">
              <h2 class="fw-bolder mb-25">
                {{ $widgets['average_turnaround_hours'] !== null ? number_format((float) $widgets['average_turnaround_hours'], 0) : '0' }}
              </h2>
              <p class="card-text mb-0">{{ __('Average turnaround (hours)') }}</p>
            </div>
            <span class="avatar bg-light-warning p-50">
              <span class="avatar-content">
                <i data-feather="clock" class="font-medium-4"></i>
              </span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row match-height">
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-25">{{ number_format($widgets['total_requests']) }}</h4>
          <p class="card-text mb-0">{{ __('Valuation requests') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-25">{{ number_format($widgets['linked_properties']) }}</h4>
          <p class="card-text mb-0">{{ __('Linked properties') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-25">{{ number_format($widgets['qima_uploaded']) }}</h4>
          <p class="card-text mb-0">{{ __('Uploaded to Qima') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-25">{{ number_format($widgets['pending_evaluation']) }}</h4>
          <p class="card-text mb-0">{{ __('Pending evaluation') }}</p>
        </div>
      </div>
    </div>

    @isset($widgets['my_assigned'])
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-25">{{ number_format($widgets['my_assigned']) }}</h4>
            <p class="card-text mb-0">{{ __('My assigned requests') }}</p>
          </div>
        </div>
      </div>
    @endisset

    @isset($widgets['my_coordinated'])
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-25">{{ number_format($widgets['my_coordinated']) }}</h4>
            <p class="card-text mb-0">{{ __('My coordinated requests') }}</p>
          </div>
        </div>
      </div>
    @endisset

    @can('valuation_request.view')
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-25">{{ number_format($widgets['quarantine']) }}</h4>
            <p class="card-text mb-0">{{ __('Import quarantine rows') }}</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-25">
              {{ number_format($widgets['pictures_missing']) }}
              <small class="text-muted">/ {{ number_format($widgets['pictures_total']) }}</small>
            </h4>
            <p class="card-text mb-0">{{ __('Pictures missing on disk') }}</p>
          </div>
        </div>
      </div>
    @endcan
  </div>

  <div class="row match-height">
    <div class="col-lg-8 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Valuations per month') }}</h4>
        </div>
        <div class="card-body">
          <div
            id="dashboard-monthly-chart"
            class="dashboard-chart"
            data-labels='@json($charts['monthly']['labels'])'
            data-values='@json($charts['monthly']['values'])'
            data-series-name="{{ __('Valuations') }}"
          ></div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">{{ __('Requests by status') }}</h4>
        </div>
        <div class="card-body">
          <div
            id="dashboard-status-chart"
            class="dashboard-chart"
            data-labels='@json($charts['by_state']['labels'])'
            data-values='@json($charts['by_state']['values'])'
          ></div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
