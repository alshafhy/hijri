<div class="demo-inline-spacing">
    {{ $before ?? '' }}

    @if ($actionname === 'index' && $createPermission)
        @can($createPermission)
            <a class="btn btn-primary float-end" href="{{ route($createRoute) }}">
                @lang('crud.add_new')
            </a>
        @endcan
    @endif

    @if ($actionname === 'show')
        @if ($key !== '' && $editPermission)
            @can($editPermission)
                <a class="btn btn-info float-end round" href="{{ route($editRoute, $key) }}">
                    @lang('crud.edit')
                </a>
            @endcan
        @endif
        @if ($viewPermission)
            @can($viewPermission)
                <a class="btn btn-warning float-end round" href="{{ route($indexRoute) }}">
                    @lang('crud.list')
                </a>
            @endcan
        @endif
    @endif

    @if ($actionname === 'edit')
        @if ($key !== '' && $viewPermission)
            @can($viewPermission)
                <a class="btn btn-info float-end round" href="{{ route($showRoute, $key) }}">
                    @lang('crud.show')
                </a>
                <a class="btn btn-warning float-end round" href="{{ route($indexRoute) }}">
                    @lang('crud.list')
                </a>
            @endcan
        @endif

        @if ($key !== '' && $deletePermission)
            @can($deletePermission)
                {!! html()->form('DELETE', route($destroyRoute, $key))->open() !!}
                {!! html()
                    ->button(__('crud.delete'), 'submit')
                    ->class('btn btn-danger round')
                    ->attribute('onclick', "return confirm('".__('crud.are_you_sure_to_delete_this_row')."')")
                !!}
                {!! html()->form()->close() !!}
            @endcan
        @endif

        {{ $slot ?? '' }}
        {{ $after ?? '' }}
    @endif
</div>
