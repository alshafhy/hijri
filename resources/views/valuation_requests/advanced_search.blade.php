@extends('layouts/contentLayoutMaster')

@section('title', __('Advanced search'))

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header">
      <h4 class="card-title mb-0">{{ __('Advanced search') }}</h4>
    </div>
    <form method="get" action="{{ route('dashboard.valuation-requests.advanced-search.results') }}">
      <div class="card-body">
        <div class="row g-1">
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
          <div class="col-md-3">
            <label class="form-label">{{ __('City') }}</label>
            <select name="city_id" class="form-select">
              <option value="">{{ __('All') }}</option>
              @foreach ($cities as $id => $name)
                <option value="{{ $id }}" @selected(($filters['city_id'] ?? '') == $id)>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Property kind') }}</label>
            <input type="text" name="property_kind" class="form-control" value="{{ $filters['property_kind'] ?? '' }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Property type') }}</label>
            <input type="text" name="property_type" class="form-control" value="{{ $filters['property_type'] ?? '' }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('From date') }}</label>
            <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('To date') }}</label>
            <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Qima') }}</label>
            <select name="uploaded_on_qima" class="form-select">
              <option value="">{{ __('All') }}</option>
              <option value="1" @selected(($filters['uploaded_on_qima'] ?? '') === '1' || ($filters['uploaded_on_qima'] ?? '') === 1)>{{ __('Yes') }}</option>
              <option value="0" @selected(($filters['uploaded_on_qima'] ?? '') === '0' || ($filters['uploaded_on_qima'] ?? '') === 0)>{{ __('No') }}</option>
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <div class="form-check mb-50">
              <input class="form-check-input" type="checkbox" name="approved_only" value="1" id="approved_only"
                     @checked(!empty($filters['approved_only']))>
              <label class="form-check-label" for="approved_only">{{ __('Approved only') }}</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
        <a href="{{ route('dashboard.valuation-requests.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
      </div>
    </form>
  </div>

  @if ($requests !== null)
    <div class="card">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>{{ __('Reference') }}</th>
              <th>{{ __('Number') }}</th>
              <th>{{ __('State') }}</th>
              <th>{{ __('Customer') }}</th>
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
                  <a href="{{ route('dashboard.valuation-requests.show', $item) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">{{ __('No results') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">{{ $requests->links() }}</div>
    </div>
  @endif
</section>
@endsection
