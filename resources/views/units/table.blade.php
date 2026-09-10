<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($units as $unit)
        <tr>
            <td>{{ $unit->id }}</td>
            <td>{{ $unit->name }}</td>
            <td>
                <a href="{{ route('dashboard.units.show', $unit->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                <a href="{{ route('dashboard.units.edit', $unit->id) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                @can('unit.delete')
                <form action="{{ route('dashboard.units.destroy', $unit->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center">{{ __('No units found') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $units->firstItem() ?? 0 }} {{ __('to') }} {{ $units->lastItem() ?? 0 }} {{ __('of') }} {{ $units->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $units->links() }}
    </div>
</div>