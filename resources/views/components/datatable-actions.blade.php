<div>
    {!! html()->form('DELETE', route($screenName.'.destroy', $rowID))->open() !!}
    <div class="btn-group">
        @can($screenName === 'users' ? 'user.view' : 'role.view')
            <a href="{{ route($screenName.'.show', $rowID) }}" class="btn btn-outline-primary btn-sm">
                <i data-feather="eye"></i>
            </a>
        @endcan

        @can($screenName === 'users' ? 'user.edit' : 'role.edit')
            <a href="{{ route($screenName.'.edit', $rowID) }}" class="btn btn-outline-warning btn-sm">
                <i data-feather="edit"></i>
            </a>
        @endcan

        @can($screenName === 'users' ? 'user.delete' : 'role.delete')
            {!! html()
                ->button('<i data-feather="trash"></i>', 'submit')
                ->class('btn btn-outline-danger btn-sm')
                ->attribute('onclick', 'return confirm("'.__('crud.are_you_sure_to_delete_this_row').'")')
            !!}
        @endcan
    </div>
    {!! html()->form()->close() !!}
</div>
