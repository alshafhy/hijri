@php
    $currentSort = $sort ?? request('sort', 'id');
    $currentDir = $dir ?? request('dir', 'desc');
    $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $query = array_merge(request()->except(['page']), ['sort' => $column, 'dir' => $nextDir]);
    $arrow = '';
    if ($currentSort === $column) {
        $arrow = $currentDir === 'asc' ? ' ↑' : ' ↓';
    }
@endphp
<a href="{{ request()->url().'?'.http_build_query($query) }}" class="text-body text-decoration-none">
  {{ $label }}{{ $arrow }}
</a>
