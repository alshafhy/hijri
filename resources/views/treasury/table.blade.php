<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>{{ __('ID') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Notes') }}</th>
            <th>{{ __('Created By') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $transaction)
        <tr>
            <td>{{ $transaction->id }}</td>
            <td>{{ $transaction->transaction_date }}</td>
            <td>
                <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdraw' ? 'warning' : 'danger') }}">
                    {{ __($transaction->type) }}
                </span>
            </td>
            <td>{{ number_format($transaction->amount, 2) }}</td>
            <td>{{ $transaction->notes ?? '-' }}</td>
            <td>{{ $transaction->createdBy->name ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">{{ __('No transactions found') }}</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        {{ __('Showing') }} {{ $transactions->firstItem() ?? 0 }} {{ __('to') }} {{ $transactions->lastItem() ?? 0 }} {{ __('of') }} {{ $transactions->total() }} {{ __('results') }}
    </div>
    <div>
        {{ $transactions->links() }}
    </div>
</div>