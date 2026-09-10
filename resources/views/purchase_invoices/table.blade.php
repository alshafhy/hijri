<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Invoice Number') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Supplier') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Total') }}</th>
            <th>{{ __('Paid') }}</th>
            <th>{{ __('Due') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $invoice)
        <tr>
            <td>{{ $invoice->id }}</td>
            <td>{{ $invoice->invoice_number }}</td>
            <td>{{ $invoice->invoice_date }}</td>
            <td>{{ $invoice->supplier->name ?? '-' }}</td>
            <td>{{ $invoice->branch->name ?? '-' }}</td>
            <td>{{ number_format($invoice->total_amount, 2) }}</td>
            <td>{{ number_format($invoice->paid_amount, 2) }}</td>
            <td>{{ number_format($invoice->due_amount, 2) }}</td>
            <td>
                <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'danger') }}">
                    {{ __($invoice->status) }}
                </span>
            </td>
            <td>
                <a href="{{ route('dashboard.purchase-invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                @can('purchase_invoice.delete')
                <form action="{{ route('dashboard.purchase-invoices.destroy', $invoice->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">{{ __('No invoices found') }}.</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $invoices->firstItem() ?? 0 }} {{ __('to') }} {{ $invoices->lastItem() ?? 0 }} {{ __('of') }} {{ $invoices->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $invoices->links() }}
    </div>
</div>