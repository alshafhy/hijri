@extends('layouts/contentLayoutMaster')

@section('title', __('Dashboard'))

@section('content')
<section class="dashboard-analytics">
  <div class="row match-height">
    <div class="col-12 mb-1">
      <h2 class="mb-0">{{ __('Dashboard') }}</h2>
      <p class="text-muted">{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</p>
    </div>

    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">{{ number_format($widgets['total_requests']) }}</h4>
          <p class="card-text">{{ __('Valuation requests') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">{{ number_format($widgets['linked_properties']) }}</h4>
          <p class="card-text">{{ __('Linked properties') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">{{ number_format($widgets['qima_uploaded']) }}</h4>
          <p class="card-text">{{ __('Uploaded to Qima') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">{{ number_format($widgets['pending_evaluation']) }}</h4>
          <p class="card-text">{{ __('Pending evaluation') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">{{ number_format($widgets['approved_count']) }}</h4>
          <p class="card-text">{{ __('Approved valuations') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">{{ number_format($widgets['total_valued_area'], 2) }}</h4>
          <p class="card-text">{{ __('Total valued area') }}</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="fw-bolder mb-0">
            {{ $widgets['average_turnaround_hours'] !== null ? number_format($widgets['average_turnaround_hours'], 2) : '—' }}
          </h4>
          <p class="card-text">{{ __('Average turnaround (hours)') }}</p>
        </div>
      </div>
    </div>

    @isset($widgets['my_assigned'])
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-0">{{ number_format($widgets['my_assigned']) }}</h4>
            <p class="card-text">{{ __('My assigned requests') }}</p>
          </div>
        </div>
      </div>
    @endisset

    @isset($widgets['my_coordinated'])
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-0">{{ number_format($widgets['my_coordinated']) }}</h4>
            <p class="card-text">{{ __('My coordinated requests') }}</p>
          </div>
        </div>
      </div>
    @endisset

    @can('valuation_request.view')
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="fw-bolder mb-0">{{ number_format($widgets['quarantine']) }}</h4>
            <p class="card-text">{{ __('Import quarantine rows') }}</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="card border-warning">
          <div class="card-body">
            <h4 class="fw-bolder mb-0">
              {{ number_format($widgets['pictures_missing']) }}
              <small class="text-muted">/ {{ number_format($widgets['pictures_total']) }}</small>
            </h4>
            <p class="card-text">{{ __('Pictures missing on disk') }}</p>
            <p class="small text-muted mb-0">{{ __('Run legacy:verify-pictures after mounting LEGACY_PICTURE_PATHS') }}</p>
          </div>
        </div>
      </div>
    @endcan
  </div>
</section>
@endsection
