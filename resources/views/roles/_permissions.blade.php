<div class="card mt-2">
    <div class="card-header">
        <h4 class="card-title">{{ __('models/roles.fields.permissions') ?? __('Permissions') }}</h4>
    </div>
    <div class="card-body">
        {!! html()->modelForm($role, 'PATCH', route('dashboard.roles.update', $role))->id('permissions-frm')->open() !!}
        {!! html()->hidden('objectId', $objectId ?? '') !!}

        <table class="table table-borderless">
            <tbody>
                @foreach ($nodes ?? [] as $node)
                    <tr>
                        <td>
                            {{ $node->comp_ar_label ?: $node->comp_name }}
                            @if ($node->route_name)
                                <small class="text-muted">({{ $node->route_name }})</small>
                            @endif
                        </td>
                        @foreach ($permission ?? [] as $value)
                            @if ((int) $value->system_component_id === (int) $node->id)
                                <td>
                                    <div class="form-check">
                                        {!! html()
                                            ->checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions ?? [], true))
                                            ->class('form-check-input')
                                            ->id('permission-'.$value->id)
                                        !!}
                                        <label class="form-check-label" for="permission-{{ $value->id }}">
                                            {{ \App\Utils\PermissionsUtil::generatePermLabel($value->name) }}
                                        </label>
                                    </div>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="card-footer px-0">
            {!! html()->submit(__('crud.save'))->class('btn btn-primary') !!}
            <a href="{{ route('dashboard.roles.index') }}" class="btn btn-default">
                @lang('crud.cancel')
            </a>
        </div>
        {!! html()->closeModelForm() !!}
    </div>
</div>
