@extends('layouts.app')

@section('title', __('models/users.plural'))

@section('breadcrumbs', __('models/users.plural'))

@section('content')
    @include('flash::message')
    @include('valuation_requests.partials.flash')
    <div class="clearfix"></div>
    <div class="row" id="table-bordered">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap gap-1 justify-content-between">
                    <span class="text-muted">{{ __('Staff members') }}: {{ $staffCount }} / {{ $staffLimit }}</span>
                    <div class="d-flex gap-50">
                        @can('viewAny', App\Models\User::class)
                            <a href="{{ route('dashboard.support-requests.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Support requests') }}</a>
                        @endcan
                        @can('create', App\Models\User::class)
                            @if ($staffCount < $staffLimit)
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inviteStaffModal">{{ __('Invite staff member') }}</button>
                            @endif
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @include('users.table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('create', App\Models\User::class)
        <div class="modal fade" id="inviteStaffModal" tabindex="-1" aria-labelledby="inviteStaffModal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('dashboard.users.invite') }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="inviteStaffModal-title">{{ __('Invite staff member') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-1">
                            <label class="form-label" for="invite-role">{{ __('Job') }}</label>
                            <select name="role" id="invite-role" class="form-select" required>
                                <option value="coordinator" @selected(old('role') === 'coordinator')>{{ __('Coordinator') }}</option>
                                <option value="evaluator" @selected(old('role') === 'evaluator')>{{ __('Evaluator') }}</option>
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="invite-name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="invite-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="invite-email">{{ __('Email') }}</label>
                            <input type="email" name="email" id="invite-email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required maxlength="255">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="invite-phone">{{ __('Phone') }}</label>
                            <input type="text" name="phone_number" id="invite-phone" class="form-control" value="{{ old('phone_number') }}" maxlength="30">
                        </div>
                        <small class="text-muted d-block mt-1">{{ __('An activation link will be emailed to the new member.') }}</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Send invitation') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @if ($errors->hasAny(['role', 'name', 'email']))
            @push('page-script')
                <script>document.addEventListener('DOMContentLoaded', function () { new bootstrap.Modal(document.getElementById('inviteStaffModal')).show(); });</script>
            @endpush
        @endif
    @endcan
@endsection
