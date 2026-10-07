@extends('layouts/contentLayoutMaster')

@section('title', __('Personal page'))

@section('content')
<section class="dashboard-ecommerce">
  <div class="row mb-2">
    <div class="col-12">
      <h2 class="mb-25">{{ __('Personal page') }}</h2>
      <p class="text-muted mb-0">{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</p>
    </div>
  </div>

  {{-- Vuexy-style soft KPI cards --}}
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

  {{-- Quick actions as separate soft buttons inside a card --}}
  <div class="card">
    <div class="card-body py-1">
      <div class="row g-1">
        @can('create', App\Models\ValuationRequest::class)
          <div class="col-md-3 col-sm-6">
            <a href="{{ route('dashboard.valuation-requests.create') }}" class="btn btn-primary w-100 rounded">{{ __('New valuation') }}</a>
          </div>
        @endcan
        @can('viewAny', App\Models\ValuationRequest::class)
          <div class="col-md-3 col-sm-6">
            <a href="{{ route('dashboard.valuation-requests.index') }}" class="btn btn-outline-primary w-100">{{ __('Valuations board') }}</a>
          </div>
        @endcan
        @can('viewAny', App\Models\User::class)
          <div class="col-md-3 col-sm-6">
            <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-primary w-100">{{ __('Users board') }}</a>
          </div>
        @endcan
        @can('viewLogs', App\Models\ValuationRequest::class)
          <div class="col-md-3 col-sm-6">
            <a href="{{ route('dashboard.valuation-requests.activity-logs') }}" class="btn btn-outline-secondary w-100">{{ __('Events board') }}</a>
          </div>
        @endcan
      </div>
    </div>
  </div>

  @can('viewAny', App\Models\ValuationRequest::class)
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">{{ __('Latest valuations') }}</h4>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{ __('Reference') }}</th>
              <th>{{ __('City') }}</th>
              <th>{{ __('Neighborhood') }}</th>
              <th>{{ __('Customer name') }}</th>
              <th>{{ __('Valuation date') }}</th>
              <th>{{ __('State') }}</th>
              <th>{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($latestValuations as $item)
              @php
                $location = $item->property?->location;
                $cityName = $location?->city?->name_ar ?: ($location?->city?->name_en ?? '—');
                $neighborhoodName = $location?->neighborhood?->name_ar ?: ($location?->neighborhood?->name_en ?? '—');
                $valuationDate = $item->started_at
                  ?? $item->property?->evaluation_date
                  ?? $item->created_at;
              @endphp
              <tr>
                <td>{{ $item->reference ?? '—' }}</td>
                <td>{{ $cityName }}</td>
                <td>{{ $neighborhoodName }}</td>
                <td>{{ $item->property?->customer_name ?? '—' }}</td>
                <td>{{ optional($valuationDate)->format('Y/m/d') ?? '—' }}</td>
                <td><span class="badge rounded-pill bg-light-primary">{{ $item->stateLabel() }}</span></td>
                <td>
                  <x-valuation.details-button :request="$item" />
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-2">{{ __('No valuations under evaluation') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endcan

  @if ($recentUsers !== null)
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ __('Users board') }}</h4>
        <a href="{{ route('dashboard.users.index') }}" class="btn btn-sm btn-outline-primary rounded">{{ __('View all') }}</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{ __('Employee name') }}</th>
              <th>{{ __('Email') }}</th>
              <th>{{ __('Username') }}</th>
              <th>{{ __('Job title') }}</th>
              <th>{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentUsers as $member)
              <tr>
                <td>{{ $member->name }}</td>
                <td>{{ $member->email }}</td>
                <td>{{ $member->username }}</td>
                <td>{{ $member->roles->pluck('name')->map(fn ($role) => __($role))->implode(' / ') ?: '—' }}</td>
                <td>
                  <a href="{{ route('dashboard.users.show', $member) }}" class="btn btn-sm btn-outline-primary rounded">{{ __('View') }}</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-2">{{ __('No users yet') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if ($recentLogs !== null)
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ __('Latest events') }}</h4>
        <a href="{{ route('dashboard.valuation-requests.activity-logs') }}" class="btn btn-sm btn-outline-primary rounded">{{ __('Events board') }}</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{ __('Type') }}</th>
              <th>{{ __('Description') }}</th>
              <th>{{ __('Reference') }}</th>
              <th>{{ __('Subject') }}</th>
              <th>{{ __('By') }}</th>
              <th>{{ __('Event time') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentLogs as $log)
              @php
                $subject = $log->subject;
                $reference = $subject instanceof \App\Models\ValuationRequest
                  ? ($subject->reference ?? $subject->id)
                  : ($log->subject_id ?? '—');
              @endphp
              <tr>
                <td><span class="badge rounded-pill bg-light-info">{{ data_get($log->properties, 'event') ?? $log->event ?? '—' }}</span></td>
                <td>{{ $log->description }}</td>
                <td>{{ $reference }}</td>
                <td>
                  @if ($log->subject_id)
                    <a href="{{ route('dashboard.valuation-requests.show', $log->subject_id) }}">#{{ $log->subject_id }}</a>
                  @else
                    —
                  @endif
                </td>
                <td>{{ $log->causer?->name ?? '—' }}</td>
                <td>{{ optional($log->created_at)->format('Y/m/d - h:i A') ?? '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-2">{{ __('No events yet') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <div class="row match-height">
    <div class="col-12 mb-1">
      <h4 class="mb-0">{{ __('Additional metrics') }}</h4>
    </div>

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
</section>
@endsection
