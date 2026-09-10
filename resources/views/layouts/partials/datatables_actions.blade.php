{{-- Legacy datatable actions stub; use components.datatable-actions --}}
@include('components.datatable-actions', [
    'screenName' => $screen_name ?? $screenName ?? 'users',
    'rowID' => $id ?? $rowID ?? null,
    'buttons' => $buttons ?? ['show', 'edit', 'delete'],
])
