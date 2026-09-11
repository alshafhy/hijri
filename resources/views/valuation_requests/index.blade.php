@extends('layouts/contentLayoutMaster')

@section('title', __('Valuation requests'))

@section('content')
<section>
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card mb-2">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-1">
      <h4 class="card-title mb-0">{{ __('Valuation requests') }}</h4>
      <div class="d-flex flex-wrap gap-1">
        <form method="get" action="{{ route('dashboard.valuation-requests.quick-search') }}" class="d-flex gap-1">
          <input type="text" name="q" class="form-control form-control-sm" placeholder="{{ __('Quick search by reference/number') }}" value="{{ request('q') }}">
          <button class="btn btn-sm btn-outline-primary" type="submit">{{ __('Search') }}</button>
        </form>
        @can('create', App\Models\ValuationRequest::class)
          <a href="{{ route('dashboard.valuation-requests.create') }}" class="btn btn-sm btn-primary">{{ __('Create valuation') }}</a>
        @endcan
        @can('advancedSearch', App\Models\ValuationRequest::class)
          <a href="{{ route('dashboard.valuation-requests.advanced-search') }}" class="btn btn-sm btn-outline-secondary">{{ __('Advanced search') }}</a>
        @endcan
      </div>
    </div>
    <div class="card-body">
      <form method="get" class="row g-1 align-items-end">
        <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
        <input type="hidden" name="dir" value="{{ $dir ?? request('dir', 'desc') }}">
        <div class="col-md-3">
          <label class="form-label">{{ __('Search') }}</label>
          <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}">
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('State') }}</label>
          <select name="state" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach (\App\Enums\Valuation\RequestState::filterOptions() as $value => $label)
              <option value="{{ $value }}" @selected(($filters['state'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('Evaluator') }}</label>
          <select name="evaluator_user_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach ($evaluators as $id => $name)
              <option value="{{ $id }}" @selected(($filters['evaluator_user_id'] ?? '') == $id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('Coordinator') }}</label>
          <select name="coordinator_user_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach ($coordinators as $id => $name)
              <option value="{{ $id }}" @selected(($filters['coordinator_user_id'] ?? '') == $id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('Qima') }}</label>
          <select name="uploaded_on_qima" class="form-select">
            <option value="">{{ __('All') }}</option>
            <option value="1" @selected(($filters['uploaded_on_qima'] ?? '') === '1')>{{ __('Yes') }}</option>
            <option value="0" @selected(($filters['uploaded_on_qima'] ?? '') === '0')>{{ __('No') }}</option>
          </select>
        </div>
        <div class="col-md-1">
          <button class="btn btn-primary w-100" type="submit">{{ __('Filter') }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>@include('components.sortable-th', ['column' => 'id', 'label' => '#'])</th>
            <th>@include('components.sortable-th', ['column' => 'reference', 'label' => __('Reference')])</th>
            <th>@include('components.sortable-th', ['column' => 'number', 'label' => __('Number')])</th>
            <th>@include('components.sortable-th', ['column' => 'state', 'label' => __('State')])</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Qima') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($requests as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->reference }}</td>
              <td>{{ $item->number }}</td>
              <td>{{ $item->stateLabel() }}</td>
              <td>{{ $item->property?->customer_name }}</td>
              <td>
                @if ($item->uploaded_on_qima)
                  <span class="badge bg-success">{{ __('Uploaded to Qima') }}</span>
                @else
                  <span class="badge bg-secondary">—</span>
                @endif
              </td>
              <td class="text-nowrap">
                <a href="{{ route('dashboard.valuation-requests.show', $item) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                @can('update', $item)
                  <a href="{{ route('dashboard.valuation-requests.edit', $item) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">{{ __('No results') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $requests->links() }}</div>
  </div>
</section>
@endsection
