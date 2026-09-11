@extends('layouts/contentLayoutMaster')

@section('title', __('Contracts'))

@section('content')
<section>
  <div class="card">
    <div class="card-header"><h4 class="card-title">{{ __('Contracts') }}</h4></div>
    <div class="card-body">
      <form method="GET" class="row g-1 mb-2">
        <div class="col-md-3">
          <input type="number" name="contractor_id" value="{{ request('contractor_id') }}" class="form-control" placeholder="{{ __('Contractor') }} #">
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
            <th>#</th>
            <th>{{ __('Contractor') }}</th>
            <th>{{ __('Valuation request') }}</th>
            <th>{{ __('Fees') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($contracts as $contract)
            <tr>
              <td>{{ $contract->id }}</td>
              <td>{{ $contract->contractor?->name }}</td>
              <td>{{ $contract->valuationRequest?->number }}</td>
              <td>{{ $contract->contractor?->fees }}</td>
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
