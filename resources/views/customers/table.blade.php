<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Total Invoiced') }}</th>
            <th>{{ __('Paid') }}</th>
            <th>{{ __('Current Debt') }}</th>
            <th>{{ __('Credit Limit') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->branch->name ?? '-' }}</td>
            <td>{{ number_format($customer->total_invoiced, 2) }}</td>
            <td>{{ number_format($customer->paid_amount, 2) }}</td>
            <td>
                <span class="badge bg-{{ $customer->current_debt > 0 ? 'warning' : 'success' }}">
                    {{ number_format($customer->current_debt, 2) }}
                </span>
            </td>
            <td>{{ $customer->credit_limit > 0 ? number_format($customer->credit_limit, 2) : __('No Limit') }}</td>
            <td>
                <span class="badge bg-{{ $customer->is_active ? 'success' : 'danger' }}">
                    {{ $customer->is_active ? __('Active') : __('Inactive') }}
                </span>
            </td>
            <td>
                <a href="{{ route('dashboard.customers.show', $customer->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                <a href="{{ route('dashboard.customers.edit', $customer->id) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                @can('customer.delete')
                <form action="{{ route('dashboard.customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">{{ __('No customers found') }}.</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $customers->firstItem() ?? 0 }} {{ __('to') }} {{ $customers->lastItem() ?? 0 }} {{ __('of') }} {{ $customers->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $customers->links() }}
    </div>
</div>