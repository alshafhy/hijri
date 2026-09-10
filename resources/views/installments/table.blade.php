<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __("Customer") }}</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($installments as $installment)
        <tr>
            <td>{{ $installment->id }}</td>
            <td>{{ $installment->customer->name ?? $installment->client_name }}</td>
            <td>{{ number_format($installment->amount, 2) }}</td>
            <td>{{ $installment->collect_date }}</td>
            <td>
                <span class="badge bg-{{ $installment->status === 'paid' ? 'success' : 'warning' }}">
                    {{ $installment->status }}
                </span>
            </td>
            <td>
                <a href="{{ route('dashboard.installments.show', $installment->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                @if($installment->status !== 'paid')
                <form action="{{ route('dashboard.installments.collect', $installment->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Collect</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">{{ __('No installments found') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $installments->firstItem() ?? 0 }} {{ __('to') }} {{ $installments->lastItem() ?? 0 }} {{ __('of') }} {{ $installments->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $installments->links() }}
    </div>
</div>