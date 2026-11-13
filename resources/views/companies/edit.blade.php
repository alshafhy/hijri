@extends('layouts/contentLayoutMaster')

@section('title', __('Company profile'))

@section('content')
<section>
  <div class="card mb-2">
    <div class="card-header"><h4 class="card-title">{{ __('Update profile') }}</h4></div>
    @can('update', $company)
      <form method="POST" action="{{ route('dashboard.companies.profile', $company) }}">
        @csrf
        @method('PUT')
        <div class="card-body row g-2">
          <div class="col-md-6">
            <label class="form-label">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-control @error('name') is-invalid @enderror" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('English name') }}</label>
            <input type="text" name="name_en" value="{{ old('name_en', $company->name_en) }}" class="form-control @error('name_en') is-invalid @enderror">
            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Phone') }}</label>
            <input type="text" name="phone_number" value="{{ old('phone_number', $company->phone_number) }}" class="form-control @error('phone_number') is-invalid @enderror">
            @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Address') }}</label>
            <input type="text" name="address" value="{{ old('address', $company->address) }}" class="form-control @error('address') is-invalid @enderror">
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Membership number') }}</label>
            <input type="text" name="company_membership_number" value="{{ old('company_membership_number', $company->company_membership_number) }}" class="form-control @error('company_membership_number') is-invalid @enderror">
            @error('company_membership_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Registration number') }}</label>
            <input type="text" name="company_reg_number" value="{{ old('company_reg_number', $company->company_reg_number) }}" class="form-control @error('company_reg_number') is-invalid @enderror">
            @error('company_reg_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="membership_date">{{ __('Membership date') }}</label>
            <input type="date" name="membership_date" id="membership_date" value="{{ old('membership_date', $company->membership_date?->format('Y-m-d')) }}" class="form-control @error('membership_date') is-invalid @enderror">
            @error('membership_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label" for="accreditation_end_date">{{ __('Accreditation end date') }}</label>
            <input type="date" name="accreditation_end_date" id="accreditation_end_date" value="{{ old('accreditation_end_date', $company->accreditation_end_date?->format('Y-m-d')) }}" class="form-control @error('accreditation_end_date') is-invalid @enderror">
            @error('accreditation_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
      </form>
    @else
      <div class="card-body row">
        <div class="col-md-6"><strong>{{ __('Name') }}:</strong> {{ $company->name }}</div>
        <div class="col-md-6"><strong>{{ __('English name') }}:</strong> {{ $company->name_en }}</div>
        <div class="col-md-6"><strong>{{ __('Phone') }}:</strong> {{ $company->phone_number }}</div>
        <div class="col-md-6"><strong>{{ __('Address') }}:</strong> {{ $company->address }}</div>
      </div>
    @endcan
  </div>

  @can('update', $company)
    <div class="card mb-2">
      <div class="card-header"><h4 class="card-title">{{ __('Default shares') }}</h4></div>
      <form method="POST" action="{{ route('dashboard.companies.shares', $company) }}">
        @csrf
        @method('PUT')
        <div class="card-body row g-2">
          <div class="col-md-4">
            <label class="form-label">{{ __('Coordinator share') }}</label>
            <input type="number" name="default_coordinator_share" min="0" max="100" value="{{ old('default_coordinator_share', $company->default_coordinator_share) }}" class="form-control @error('default_coordinator_share') is-invalid @enderror" required>
            @error('default_coordinator_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Evaluator share') }}</label>
            <input type="number" name="default_evaluator_share" min="0" max="100" value="{{ old('default_evaluator_share', $company->default_evaluator_share) }}" class="form-control @error('default_evaluator_share') is-invalid @enderror" required>
            @error('default_evaluator_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Manager share') }}</label>
            <input type="number" name="default_manager_share" min="0" max="100" value="{{ old('default_manager_share', $company->default_manager_share) }}" class="form-control @error('default_manager_share') is-invalid @enderror" required>
            @error('default_manager_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">{{ __('Update shares') }}</button>
        </div>
      </form>
    </div>

    <div class="card mb-2">
      <div class="card-header"><h4 class="card-title">{{ __('Turnaround time (SLA)') }}</h4></div>
      <form method="POST" action="{{ route('dashboard.companies.sla', $company) }}">
        @csrf
        @method('PUT')
        <div class="card-body row g-2">
          <p class="text-muted mb-0">{{ __('The details button turns red once a valuation stays longer than these hours in its stage.') }}</p>
          @foreach (['request_sla_hours' => __('New request time (hours)'), 'evaluation_sla_hours' => __('Under evaluation time (hours)')] as $field => $label)
            <div class="col-md-6">
              <label class="form-label" for="{{ $field }}">{{ $label }}</label>
              <input type="number" name="{{ $field }}" id="{{ $field }}" min="1" max="8760" value="{{ old($field, $company->{$field}) }}" class="form-control @error($field) is-invalid @enderror" required>
              @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endforeach
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="card-header"><h4 class="card-title">{{ __('Branding') }}</h4></div>
      <div class="card-body">
        <div class="row g-3">
          @foreach (['logo' => __('Logo'), 'report_logo' => __('Report logo'), 'signature' => __('Signature'), 'stamp' => __('Stamp')] as $type => $label)
            <div class="col-md-3">
              <h6>{{ $label }}</h6>
              @if ($brandingPreviews[$type] ?? null)
                <img src="{{ $brandingPreviews[$type] }}" alt="{{ $label }}" class="img-fluid img-thumbnail mb-1">
              @endif
              <form method="POST" action="{{ route('dashboard.companies.branding', $company) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="file" name="image" class="form-control mb-1" accept="image/*" required>
                <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Upload') }}</button>
              </form>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  @endcan
</section>
@endsection
