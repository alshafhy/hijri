@extends('layouts/contentLayoutMaster')

@section('title', $user->name)

@section('vendor-script')
  <script src="{{ asset('vendors/js/charts/apexcharts.min.js') }}"></script>
@endsection

@section('content')
@php
  $roleLabels = ['coordinator' => __('Coordinator'), 'evaluator' => __('Evaluator'), 'manager' => __('Manager'), 'admin' => __('Admin'), 'super-admin' => __('Super admin')];
  $role = $user->getRoleNames()->map(fn ($name) => $roleLabels[$name] ?? $name)->implode('، ');
  $canEdit = auth()->user()->can('update', $user) && ! auth()->user()->is($user);
@endphp
<section id="staff-profile">
  @include('valuation_requests.partials.flash')
  @if ($errors->has('member'))
    <div class="alert alert-danger p-1">{{ $errors->first('member') }}</div>
  @endif

  <div class="card">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-1">
        <div class="avatar bg-light-primary avatar-lg">
          @if ($user->avatar_path)
            <img src="{{ route('dashboard.profile.images.show', [$user, 'avatar']) }}" alt="" width="64" height="64" class="rounded">
          @else
            <span class="avatar-content fs-3">{{ mb_substr($user->name, 0, 1) }}</span>
          @endif
        </div>
        <div>
          <h4 class="mb-25">{{ $user->name }}</h4>
          <div class="text-muted">{{ $role ?: '—' }} · {{ $user->email }}</div>
          <div class="small">{{ __('Membership number') }}: {{ $user->membership_number ?: '—' }}</div>
          <div class="mt-50 d-flex gap-50 flex-wrap">
            @if ($user->hasPendingInvitation())
              <span class="badge bg-light-warning">{{ __('Invitation pending') }}</span>
            @elseif ($user->status?->isActive())
              <span class="badge bg-light-success">{{ __('Active') }}</span>
            @else
              <span class="badge bg-light-secondary">{{ __('Inactive') }}</span>
            @endif
            @if ($user->can_approve_final_request)
              <span class="badge bg-light-info">{{ __('Delegated for final approval') }}</span>
            @endif
          </div>
        </div>
      </div>

      @if ($canEdit)
        <div class="d-flex flex-wrap gap-50" id="staff-actions">
          @if ($user->hasPendingInvitation())
            <form method="POST" action="{{ route('dashboard.users.invitation.resend', $user) }}">@csrf
              <button class="btn btn-sm btn-outline-warning">{{ __('Resend invitation') }}</button>
            </form>
          @else
            <form method="POST" action="{{ route('dashboard.users.status', $user) }}">@csrf @method('PUT')
              <input type="hidden" name="active" value="{{ $user->status?->isActive() ? 0 : 1 }}">
              <button class="btn btn-sm {{ $user->status?->isActive() ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $user->status?->isActive() ? __('Deactivate') : __('Activate') }}</button>
            </form>
          @endif
          @if ($user->isStaffMember())
            <form method="POST" action="{{ route('dashboard.users.final-approval', $user) }}">@csrf @method('PUT')
              <input type="hidden" name="granted" value="{{ $user->can_approve_final_request ? 0 : 1 }}">
              <button class="btn btn-sm {{ $user->can_approve_final_request ? 'btn-outline-secondary' : 'btn-outline-info' }}">{{ $user->can_approve_final_request ? __('Revoke final approval') : __('Delegate final approval') }}</button>
            </form>
          @endif
          <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
        </div>
      @endif
    </div>
  </div>

  <div class="row">
    @foreach (['total' => [__('All valuations'), 'primary'], 'under_evaluation' => [__('Under evaluation'), 'warning'], 'evaluated' => [__('Evaluated'), 'info'], 'approved' => [__('Approved'), 'success'], 'cancelled' => [__('Cancelled'), 'danger']] as $key => [$label, $colour])
      <div class="col">
        <div class="card text-center">
          <div class="card-body">
            <h2 class="fw-bolder text-{{ $colour }} mb-25" data-counter="{{ $key }}">{{ number_format($profile['counters'][$key]) }}</h2>
            <p class="card-text mb-0">{{ $label }}</p>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Valuations per month') }}</h5></div>
        <div class="card-body"><div id="staff-monthly-chart" style="min-height: 280px;"></div></div>
      </div>
    </div>
    <div class="col-lg-4">
      @if ($canEdit)
        <div class="card">
          <div class="card-header"><h5 class="card-title mb-0">{{ __('Membership number') }}</h5></div>
          <form method="POST" action="{{ route('dashboard.users.membership', $user) }}" class="card-body">
            @csrf @method('PUT')
            <input type="text" name="membership_number" class="form-control mb-1 @error('membership_number') is-invalid @enderror" value="{{ old('membership_number', $user->membership_number) }}" maxlength="100">
            @error('membership_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <button class="btn btn-primary btn-sm">{{ __('Save') }}</button>
          </form>
        </div>
      @endif
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between"><span>{{ __('Phone') }}</span><span dir="ltr">{{ $user->phone_number ?: '—' }}</span></div>
          <div class="d-flex justify-content-between"><span>{{ __('Created at') }}</span><span>{{ $user->created_at?->format('Y/m/d') }}</span></div>
          <div class="d-flex justify-content-between"><span>{{ __('Last login') }}</span><span>{{ $user->last_login_at?->diffForHumans() ?? '—' }}</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="card-title mb-0">{{ __('Financial dues') }}</h5></div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
          <tr><th>{{ __('Reference') }}</th><th>{{ __('Valuation number') }}</th><th>{{ __('Type') }}</th><th>{{ __('Client') }}</th><th>{{ __('Fees') }}</th><th>{{ __('Coordinator') }}</th><th>{{ __('Share %') }}</th><th>{{ __('Share amount') }}</th></tr>
        </thead>
        <tbody>
          @forelse ($profile['dues'] as $row)
            <tr>
              <td><a href="{{ route('dashboard.valuation-requests.show', $row['request']) }}">{{ $row['request']->reference }}</a></td>
              <td>{{ $row['request']->number }}</td>
              <td>{{ $row['type'] }}</td>
              <td>{{ $row['client'] ?? '—' }}</td>
              <td>{{ number_format($row['fees'], 2) }}</td>
              <td>{{ $row['coordinator'] ?? '—' }}</td>
              <td>{{ $row['percent'] }}%</td>
              <td>{{ number_format($row['amount'], 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">{{ __('No financial dues yet.') }}</td></tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr class="fw-bolder"><td colspan="7">{{ __('Total dues') }}</td><td data-dues-total>{{ number_format($profile['dues_total'], 2) }}</td></tr>
        </tfoot>
      </table>
    </div>
    @if ($profile['dues']->hasPages())
      <div class="card-footer">{{ $profile['dues']->links() }}</div>
    @endif
  </div>
</section>
@endsection

@push('page-script')
  <script>
    (function () {
      var el = document.getElementById('staff-monthly-chart');
      if (!el || typeof ApexCharts === 'undefined') { return; }
      new ApexCharts(el, {
        chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [{ name: @json(__('Valuations')), data: @json($profile['monthly']['values']) }],
        xaxis: { categories: @json($profile['monthly']['labels']) },
        colors: ['#7367f0'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
        dataLabels: { enabled: false }
      }).render();
    })();
  </script>
@endpush
