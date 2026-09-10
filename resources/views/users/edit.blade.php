@extends('layouts.app')

@section('title', __('models/users.plural'))

@section('breadcrumbs', __('models/users.plural'))

@section('content')
    <div>
        @include('layouts.partials.messages')

        <div class="card">
            {!! html()->modelForm($user, 'PATCH', route('dashboard.users.update', $user))->open() !!}
            <div class="card-header">
                <h4 class="card-title">{{ __('models/users.plural') }} - @lang('crud.edit')</h4>
                @include('layouts.partials.form_toolbar', ['screen_name' => 'users', 'action_name' => 'edit'])
            </div>

            <div class="card-body">
                <div class="row">
                    @include('users.fields')
                </div>

                <div class="form-group col-sm-12 mt-2">
                    {!! html()->label(__('models/roles.plural'), 'roles-acl') !!}
                    <div class="select2-primary">
                        {!! html()
                            ->multiselect('roles', $roles_list->toArray(), $userRoles->pluck('id')->all())
                            ->id('roles-acl')
                            ->class('select2 form-select')
                        !!}
                    </div>
                </div>
            </div>

            <div class="card-footer">
                {!! html()->submit(__('crud.save'))->class('btn btn-primary') !!}
                <a href="{{ route('dashboard.users.index') }}" class="btn btn-default">
                    @lang('crud.cancel')
                </a>
            </div>
            {!! html()->closeModelForm() !!}
        </div>
    </div>
@endsection
