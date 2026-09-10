@extends('layouts.app')

@section('title', __('models/roles.plural'))

@section('breadcrumbs', __('models/roles.plural'))

@section('content')
    <div>
        @include('layouts.partials.messages')

        <div class="card">
            {!! html()->modelForm($role, 'PATCH', route('dashboard.roles.update', $role))->open() !!}
            <div class="card-header">
                <h4 class="card-title">{{ __('models/roles.plural') }} - @lang('crud.edit')</h4>
                @include('layouts.partials.form_toolbar', ['screen_name' => 'roles', 'action_name' => 'edit', 'key' => $role->id])
            </div>

            <div class="card-body">
                <div class="row">
                    @include('roles.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! html()->submit(__('crud.save'))->class('btn btn-primary') !!}
                <a href="{{ route('dashboard.roles.index') }}" class="btn btn-default">
                    @lang('crud.cancel')
                </a>
            </div>
            {!! html()->closeModelForm() !!}
        </div>

        <div class="card mt-2">
            <div class="card-header">
                <h4 class="card-title">{{ __('models/roles.fields.permissions') ?? 'Permissions' }}</h4>
            </div>
            <div class="card-body">
                <div class="form-group col-sm-12">
                    <label for="object_id">{{ __('messages.select') }}</label>
                    <select class="form-control" id="object_id" data-role-id="{{ $role->id }}">
                        <option value=""></option>
                        @foreach ($sysScreens as $sysScreen)
                            <option value="{{ $sysScreen->id }}">
                                {{ $sysScreen->comp_ar_label ?: $sysScreen->comp_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="js-permissions-partial-target"></div>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
<script src="{{ asset('js/scripts/pages/role-permissions.js') }}"></script>
@endpush
