@extends('layouts/contentLayoutMaster')

@section('title', $contractor->name)

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-1">
      <h4 class="card-title mb-0">{{ $contractor->name }}</h4>
      <div class="d-flex gap-1 flex-wrap">
        @can('update', $contractor)
          <a href="{{ route('dashboard.contractors.edit', $contractor) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
        @endcan
        <a href="{{ route('dashboard.contractors.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Back') }}</a>
      </div>
    </div>
    <div class="card-body row">
      <div class="col-md-3"><strong>{{ __('Email') }}:</strong> {{ $contractor->email }}</div>
      <div class="col-md-3"><strong>{{ __('Phone') }}:</strong> {{ $contractor->phone_number }}</div>
      <div class="col-md-3"><strong>{{ __('Fees') }}:</strong> {{ $contractor->fees }}</div>
      <div class="col-md-3"><strong>{{ __('State') }}:</strong> {{ $contractor->state }}</div>
    </div>
  </div>

  <div class="card mb-2">
    <div class="card-header"><h4 class="card-title mb-0">{{ __('Contacts') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($contacts as $contact)
            <tr>
              <td>{{ $contact->name }}</td>
              <td>{{ $contact->email }}</td>
              <td>{{ $contact->phone_number }}</td>
              <td>{{ $contact->status->label() }}</td>
              <td>
                @can('update', $contractor)
                  @if ($contact->isActive())
                    <form method="POST" action="{{ route('dashboard.contractors.contacts.deactivate', [$contractor, $contact]) }}">@csrf
                      <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Remove') }}</button>
                    </form>
                  @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ __('No contacts found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $contacts->links() }}</div>
  </div>

  <div class="card">
    <div class="card-header"><h4 class="card-title">{{ __('Contracts') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Valuation request') }}</th>
            <th>{{ __('State') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($contracts as $contract)
            <tr>
              <td>{{ $contract->id }}</td>
              <td>{{ $contract->valuationRequest?->number }}</td>
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
            <tr><td colspan="4" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $contracts->links() }}</div>
  </div>
</section>
@endsection
