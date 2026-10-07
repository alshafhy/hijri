@extends('layouts/contentLayoutMaster')

@section('title', __('Companies'))

@section('content')
<section>
  <div class="card">
    <div class="card-header"><h4 class="card-title">{{ __('Companies') }}</h4></div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($companies as $company)
            <tr>
              <td>{{ $company->id }}</td>
              <td>{{ $company->name }}</td>
              <td>{{ $company->phone_number }}</td>
              <td>
                <a href="{{ route('dashboard.companies.edit', $company) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center">{{ __('No records found') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if(method_exists($companies, 'links'))
      <div class="card-footer">{{ $companies->links() }}</div>
    @endif
  </div>
</section>
@endsection
