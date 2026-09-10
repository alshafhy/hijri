<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>Parent</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->parent->name ?? '-' }}</td>
            <td>
                <a href="{{ route('dashboard.categories.show', $category->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                <a href="{{ route('dashboard.categories.edit', $category->id) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                @can('category.delete')
                <form action="{{ route('dashboard.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">{{ __('No categories found') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $categories->firstItem() ?? 0 }} {{ __('to') }} {{ $categories->lastItem() ?? 0 }} {{ __('of') }} {{ $categories->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $categories->links() }}
    </div>
</div>