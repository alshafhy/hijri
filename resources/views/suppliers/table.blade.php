<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Total Purchased') }}</th>
            <th>{{ __('Paid') }}</th>
            <th>{{ __('Current Balance') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($suppliers as $supplier)
        <tr>
            <td>{{ $supplier->id }}</td>
            <td>{{ $supplier->name }}</td>
            <td>{{ $supplier->phone }}</td>
            <td>{{ $supplier->branch->name ?? '-' }}</td>
            <td>{{ number_format($supplier->total_purchased, 2) }}</td>
            <td>{{ number_format($supplier->paid_amount, 2) }}</td>
            <td>
                <span class="badge bg-{{ $supplier->current_balance > 0 ? 'warning' : 'success' }}">
                    {{ number_format($supplier->current_balance, 2) }}
                </span>
            </td>
            <td>
                <span class="badge bg-{{ $supplier->is_active ? 'success' : 'danger' }}">
                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td>
                <a href="{{ route('dashboard.suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                <a href="{{ route('dashboard.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                @can('supplier.delete')
                <form action="{{ route('dashboard.suppliers.destroy', $supplier->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">{{ __('No suppliers found') }}.</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $suppliers->firstItem() ?? 0 }} {{ __('to') }} {{ $suppliers->lastItem() ?? 0 }} {{ __('of') }} {{ $suppliers->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $suppliers->links() }}
    </div>
</div>