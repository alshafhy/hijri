@extends('layouts/contentLayoutMaster')

@section('title', __('Contracts'))

@section('content')
<section>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ __('Contracts') }}</h4>
      @can('create', App\Models\Contract::class)
        <a href="{{ route('dashboard.contracts.create') }}" class="btn btn-sm btn-primary">{{ __('Add contract') }}</a>
      @endcan
    </div>
    <div class="card-body">
      <form method="GET" class="row g-1 mb-2">
        <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
        <input type="hidden" name="dir" value="{{ $dir ?? request('dir', 'desc') }}">
        <div class="col-md-3">
          <select name="contractor_id" class="form-select">
            <option value="">{{ __('Contractor') }}</option>
            @foreach ($contractors as $id => $name)
              <option value="{{ $id }}" @selected((string) request('contractor_id') === (string) $id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="state" class="form-select">
            <option value="">{{ __('State') }}</option>
            <option value="0" @selected(request('state') === '0')>{{ __('Unpaid') }}</option>
            <option value="1" @selected(request('state') === '1')>{{ __('Paid') }}</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-primary" type="submit">{{ __('Filter') }}</button>
        </div>
      </form>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>@include('components.sortable-th', ['column' => 'id', 'label' => '#'])</th>
            <th>@include('components.sortable-th', ['column' => 'contractor_id', 'label' => __('Contractor')])</th>
            <th>@include('components.sortable-th', ['column' => 'valuation_request_id', 'label' => __('Valuation request')])</th>
            <th>{{ __('Fees') }}</th>
            <th>@include('components.sortable-th', ['column' => 'state', 'label' => __('State')])</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($contracts as $contract)
            <tr>
              <td>{{ $contract->id }}</td>
              <td>{{ $contract->contractor?->name }}</td>
              <td>{{ $contract->valuationRequest?->number }}</td>
              <td>{{ number_format((float) ($contract->contractor?->fees ?? 0), 2) }} {{ __('SAR') }}</td>
              <td>
                @if ($contract->isPaid())
                  <span class="badge bg-success">{{ __('Paid') }}</span>
                @else
                  <span class="badge bg-warning">{{ __('Unpaid') }}</span>
                @endif
              </td>
              <td><a href="{{ route('dashboard.contracts.show', $contract) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $contracts->links() }}</div>
  </div>
</section>
@endsection
